<?php

namespace App\Modules\TaxCore\Jobs;

use App\Modules\TaxCore\Domain\TaxCoreInvoice;
use App\Modules\TaxCore\Repository\TaxCoreInvoiceRepository;
use App\Modules\TaxCore\Service\TaxCoreAuthService;
use App\Modules\TaxCore\Service\TaxCoreInvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FiscalizeInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum number of attempts before the job is moved to dead letter.
     * Retry schedule:  1st retry → 30s,  2nd → 5min,  3rd → 30min
     */
    public int $tries = 4;

    /**
     * Timeout per attempt (seconds). TaxCore E-SDC typically responds in < 5s.
     */
    public int $timeout = 60;

    /**
     * @param array  $payload    Pre-built TaxCore v3 invoice payload (from buildPayload())
     * @param int    $invoiceId  Local DB id of the TaxCoreInvoice record (status=pending)
     * @param string $esdcId     E-SDC queue identifier — routes the job to the right worker pool
     */
    public function __construct(
        private readonly array  $payload,
        private readonly int    $invoiceId,
        private readonly string $esdcId = 'default',
    ) {
        // Route to the dedicated serial queue for this E-SDC instance.
        // Each E-SDC MUST have exactly 1 worker to avoid PIN session collisions.
        $this->onQueue("taxcore:{$esdcId}");
    }

    // -------------------------------------------------------------------------
    // Retry backoff — exponential: 30s, 5min, 30min
    // -------------------------------------------------------------------------
    public function backoff(): array
    {
        return [30, 300, 1800];
    }

    // -------------------------------------------------------------------------
    // Main execution
    // -------------------------------------------------------------------------
    public function handle(
        TaxCoreInvoiceService    $invoiceService,
        TaxCoreInvoiceRepository $invoiceRepository,
        TaxCoreAuthService       $authService,
    ): void {
        $invoice = $invoiceRepository->findById($this->invoiceId);

        if (! $invoice) {
            Log::error("FiscalizeInvoiceJob: invoice #{$this->invoiceId} not found in DB.");
            return;
        }

        // Mark as processing immediately so the status endpoint reflects activity
        $invoiceRepository->updateStatus($invoice, 'processing', [
            'attempts' => $this->attempts(),
        ]);

        try {
            $result = $invoiceService->doFiscalizeRaw($this->payload);

            $invoiceRepository->updateStatus($invoice, 'completed', [
                'taxcore_uid'       => $result['invoiceNumber']      ?? null,
                'qr_code'           => $result['verificationQRCode'] ?? null,
                'digital_signature' => $result['signature']          ?? null,
                'verification_url'  => $result['verificationUrl']    ?? null,
                'raw_response'      => $result,
                'processed_at'      => now(),
                'attempts'          => $this->attempts(),
                'error_message'     => null,
            ]);

            Log::info("FiscalizeInvoiceJob: invoice #{$this->invoiceId} fiscalized → {$result['invoiceNumber']}");

        } catch (\Throwable $e) {
            $this->handleFailure($invoiceRepository, $invoice, $e);
        }
    }

    // -------------------------------------------------------------------------
    // Final failure — all retries exhausted
    // -------------------------------------------------------------------------
    public function failed(\Throwable $e): void
    {
        Log::critical("FiscalizeInvoiceJob: invoice #{$this->invoiceId} moved to dead letter. Error: {$e->getMessage()}");

        $invoice = app(TaxCoreInvoiceRepository::class)->findById($this->invoiceId);

        if ($invoice) {
            app(TaxCoreInvoiceRepository::class)->updateStatus($invoice, 'dead_lettered', [
                'error_message' => $e->getMessage(),
                'attempts'      => $this->attempts(),
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Decide whether to retry or fail permanently based on the error type.
     *
     * - 1500 (PIN expired):  Force PIN refresh, then re-throw to trigger retry
     * - 2100 (wrong PIN):    Non-retryable — alert and die
     * - 2110 (card locked):  Non-retryable — alert and die
     * - Everything else:     Re-throw to trigger retry with backoff
     */
    private function handleFailure(
        TaxCoreInvoiceRepository $repo,
        TaxCoreInvoice           $invoice,
        \Throwable               $e,
    ): void {
        $message = $e->getMessage();

        $repo->updateStatus($invoice, 'failed', [
            'error_message' => $message,
            'attempts'      => $this->attempts(),
        ]);

        // Non-retryable errors — card issues require manual intervention
        if (str_contains($message, '2100') || str_contains($message, '2110')) {
            Log::critical("FiscalizeInvoiceJob: non-retryable TaxCore error for invoice #{$this->invoiceId}: {$message}");
            $this->fail($e);
            return;
        }

        // PIN session expired — force refresh so next attempt re-verifies PIN
        if (str_contains($message, '1500')) {
            Log::warning("FiscalizeInvoiceJob: PIN session expired (1500) for invoice #{$this->invoiceId}, forcing refresh.");
            app(TaxCoreAuthService::class)->forceRefresh();
        }

        // Re-throw to let Laravel retry with backoff()
        throw $e;
    }
}
