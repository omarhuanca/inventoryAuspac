<?php

namespace App\Modules\TaxCore\Controller;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Modules\TaxCore\Jobs\FiscalizeInvoiceJob;
use App\Modules\TaxCore\Repository\TaxCoreInvoiceRepository;
use App\Modules\TaxCore\Repository\TaxRateRepository;
use App\Modules\TaxCore\Service\TaxCoreAuthService;
use App\Modules\TaxCore\Service\TaxCoreCertificateService;
use App\Modules\TaxCore\Service\TaxCoreInvoiceService;
use App\Modules\TaxCore\Service\TaxCoreParameterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TaxCoreController extends Controller
{
    public function __construct(
        private TaxCoreCertificateService $certService,
        private TaxCoreAuthService $authService,
        private TaxCoreParameterService $parameterService,
        private TaxCoreInvoiceService $invoiceService,
        private TaxRateRepository $taxRateRepository,
        private TaxCoreInvoiceRepository $invoiceRepository,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/taxcore/status",
     *     summary="Check TaxCore connection status",
     *     tags={"TaxCore"},
     *     description="Reads the installed PFX certificate, extracts the API endpoint, and verifies that a token can be obtained from the V-SDC.",
     *     @OA\Response(
     *         response=200,
     *         description="Connection is healthy",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="TaxCore connection OK"),
     *             @OA\Property(property="statusCode", type="integer", example=200),
     *             @OA\Property(property="error", type="boolean", example=false),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="endpoint", type="string"),
     *                 @OA\Property(property="token_preview", type="string"),
     *                 @OA\Property(property="certificate", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=503, description="Connection failed")
     * )
     */
    public function status(): JsonResponse
    {
        try {
            $endpoint = $this->certService->getEndpoint();
            $token    = $this->authService->getToken();
            $certInfo = $this->certService->getCertificateInfo();

            return ApiResponse::success('TaxCore connection OK', 200, [
                'endpoint' => $endpoint,
                'token_preview' => substr($token, 0, 10) . '...',
                'certificate' => $certInfo,
            ]);
        } catch (\Throwable $e) {
            return ApiResponse::error('TaxCore connection failed: ' . $e->getMessage(), 503);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/taxcore/sync-rates",
     *     summary="Sync tax rates from TaxCore",
     *     tags={"TaxCore"},
     *     description="Calls Get Environment Parameters on the V-SDC and persists the returned tax rates in the local database.",
     *     @OA\Response(response=200, description="Tax rates synced successfully"),
     *     @OA\Response(response=503, description="Sync failed")
     * )
     */
    public function syncRates(): JsonResponse
    {
        try {
            $raw   = $this->parameterService->syncRates();
            $rates = $this->taxRateRepository->all();

            return ApiResponse::success('Tax rates synced successfully', 200, [
                'synced_count' => $rates->count(),
                'rates' => $rates,
                'raw_response' => $raw,
            ]);
        } catch (\Throwable $e) {
            return ApiResponse::error('Tax rate sync failed: ' . $e->getMessage(), 503);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/taxcore/rates",
     *     summary="List stored tax rates",
     *     tags={"TaxCore"},
     *     @OA\Response(response=200, description="List of persisted tax rates")
     * )
     */
    public function getRates(): JsonResponse
    {
        $rates = $this->taxRateRepository->all();

        return ApiResponse::success('Tax rates retrieved', 200, $rates);
    }

    /**
     * @OA\Post(
     *     path="/api/taxcore/fiscalize",
     *     summary="Fiscalize (send) an invoice to TaxCore",
     *     tags={"TaxCore"},
     *     description="Builds the TaxCore invoice payload from the request body, sends it to the V-SDC, and persists the fiscal response (QR code, digital signature, verification URL).",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"invoiceNumber","invoiceDate","items"},
     *             @OA\Property(property="invoiceNumber", type="string", example="INV-0001"),
     *             @OA\Property(property="invoiceDate", type="string", format="date-time"),
     *             @OA\Property(property="saleId", type="string", example="42"),
     *             @OA\Property(property="buyerName", type="string", example="John Doe"),
     *             @OA\Property(property="buyerTaxId", type="string", example="123456789"),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="name", type="string", example="Product A"),
     *                     @OA\Property(property="quantity", type="number", example=2),
     *                     @OA\Property(property="unitPrice", type="number", example=10.00),
     *                     @OA\Property(property="taxCode", type="string", example="A")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=202, description="Invoice queued for fiscalization"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function fiscalize(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoiceType'          => 'nullable|string',
            'transactionType'      => 'nullable|string',
            'cashier'              => 'nullable|string|max:50',
            'buyerId'              => 'nullable|string|max:20',
            'buyerCostCenterId'    => 'nullable|string|max:50',
            'invoiceNumber'        => 'nullable|string|max:60',
            'dateAndTimeOfIssue'   => 'nullable|string',
            'saleId'               => 'nullable|string|max:50',
            'esdcId'               => 'nullable|string|max:50',
            'items'                => 'required|array|min:1',
            'items.*.name'         => 'required|string',
            'items.*.quantity'     => 'required|numeric|min:0.001',
            'items.*.unitPrice'    => 'required|numeric|min:0',
            'items.*.labels'       => 'nullable|array',
            'items.*.labels.*'     => 'string',
            'items.*.taxCode'      => 'nullable|string',
            'items.*.totalAmount'  => 'nullable|numeric',
            'payment'              => 'nullable|array',
            'payment.*.paymentType' => 'required_with:payment|string',
            'payment.*.amount'      => 'required_with:payment|numeric',
        ]);

        $esdcId  = $validated['esdcId'] ?? 'default';
        $payload = $this->invoiceService->buildPayload($validated);

        // 1. Persist a pending record so the client can track it immediately
        $invoice = $this->invoiceRepository->createPending([
            'sale_id' => $validated['saleId'] ?? null,
            'esdc_id' => $esdcId,
        ]);

        // 2. Dispatch the job — returns immediately (non-blocking)
        $jobId = (string) Str::uuid();
        FiscalizeInvoiceJob::dispatch($payload, $invoice->id, $esdcId)
            ->onQueue("taxcore:{$esdcId}");

        // 3. Store the job UUID so the client can correlate
        $this->invoiceRepository->updateStatus($invoice, 'pending', [
            'queue_job_id' => $jobId,
        ]);

        return ApiResponse::success('Invoice queued for fiscalization', 202, [
            'invoice_id' => $invoice->id,
            'job_id'     => $jobId,
            'status'     => 'pending',
            'poll_url'   => url("/api/taxcore/invoices/{$invoice->id}"),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/taxcore/invoices/{id}/retry",
     *     summary="Retry a dead-lettered or failed invoice",
     *     tags={"TaxCore"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=202, description="Invoice re-queued"),
     *     @OA\Response(response=404, description="Invoice not found"),
     *     @OA\Response(response=409, description="Invoice is not in a retryable state")
     * )
     */
    public function retryInvoice(int $id): JsonResponse
    {
        $invoice = $this->invoiceRepository->findById($id);

        if (! $invoice) {
            return ApiResponse::error('Invoice not found', 404);
        }

        if (! $invoice->isFailed()) {
            return ApiResponse::error(
                "Invoice status is '{$invoice->status}' — only failed or dead_lettered invoices can be retried.",
                409
            );
        }

        $esdcId  = $invoice->esdc_id ?? 'default';
        $payload = $invoice->raw_response
            ? $this->invoiceService->buildPayload(
                array_merge($invoice->raw_response, ['items' => $invoice->raw_response['items'] ?? []])
              )
            : [];

        $this->invoiceRepository->updateStatus($invoice, 'pending', [
            'error_message' => null,
            'attempts'      => 0,
            'queued_at'     => now(),
        ]);

        FiscalizeInvoiceJob::dispatch($payload, $invoice->id, $esdcId)
            ->onQueue("taxcore:{$esdcId}");

        return ApiResponse::success('Invoice re-queued for fiscalization', 202, [
            'invoice_id' => $invoice->id,
            'status'     => 'pending',
            'poll_url'   => url("/api/taxcore/invoices/{$invoice->id}"),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/taxcore/invoices",
     *     summary="List all fiscalized invoices",
     *     tags={"TaxCore"},
     *     description="Returns a paginated list of all invoices that have been fiscalized and stored locally.",
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=20)),
     *     @OA\Response(response=200, description="Paginated list of fiscal invoices")
     * )
     */
    public function getInvoices(Request $request): JsonResponse
    {
        $perPage  = (int) $request->query('per_page', 20);
        $invoices = $this->invoiceRepository->all($perPage);

        return ApiResponse::success('Invoices retrieved', 200, $invoices);
    }

    /**
     * @OA\Get(
     *     path="/api/taxcore/invoices/{id}",
     *     summary="Get a single fiscalized invoice",
     *     tags={"TaxCore"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Invoice found"),
     *     @OA\Response(response=404, description="Invoice not found")
     * )
     */
    public function getInvoice(int $id): JsonResponse
    {
        $invoice = $this->invoiceRepository->findById($id);

        if (! $invoice) {
            return ApiResponse::error('Invoice not found', 404);
        }

        return ApiResponse::success('Invoice retrieved', 200, $invoice);
    }

    /**
     * @OA\Post(
     *     path="/api/taxcore/test-invoice",
     *     summary="Send a hardcoded test invoice to TaxCore",
     *     tags={"TaxCore"},
     *     description="Sends a sample Normal Sale invoice with one product and returns the full raw fiscal response including QR code, signature and verification URL.",
     *     @OA\Response(response=200, description="Test invoice fiscalized successfully"),
     *     @OA\Response(response=503, description="Fiscalization failed")
     * )
     */
    public function testInvoice(): JsonResponse
    {
        $testPayload = [
            'invoiceType' => 'Normal',
            'transactionType' => 'Sale',
            'cashier' => 'Test Cashier',
            'items' => [
                [
                    'name' => 'Test Product',
                    'quantity' => 1,
                    'unitPrice' => 100.00,
                    'labels' => ['E'],   // STT @ 6%
                ],
            ],
            'payment' => [
                ['paymentType' => 'Cash', 'amount' => 100.00],
            ],
        ];

        try {
            $payload = $this->invoiceService->buildPayload($testPayload);
            $invoice = $this->invoiceService->fiscalize($payload, 'test-' . now()->format('YmdHis'));

            return ApiResponse::success('Test invoice fiscalized successfully', 200, $invoice);
        } catch (\Throwable $e) {
            return ApiResponse::error('Test invoice failed: ' . $e->getMessage(), 503);
        }
    }
}
