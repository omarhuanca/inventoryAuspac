<?php

namespace App\Modules\TaxCore\Service;

use App\Modules\TaxCore\Domain\TaxCoreInvoice;
use App\Modules\TaxCore\Repository\TaxCoreInvoiceRepository;
use GuzzleHttp\Exception\ClientException;

class TaxCoreInvoiceService
{
    public function __construct(
        private TaxCoreCertificateService   $certService,
        private TaxCoreAuthService          $authService,
        private TaxCoreInvoiceRepository    $invoiceRepository
    ) {}

    /**
     * Fiscalize a sale by sending its data to TaxCore's Create Invoice endpoint.
     *
     * @param array  $invoiceData Structured invoice payload (see buildPayload()).
     * @param string $saleId      Local sale reference stored with the fiscal response.
     *
     * @return TaxCoreInvoice The persisted fiscal record.
     */
    public function fiscalize(array $invoiceData, string $saleId = ''): TaxCoreInvoice
    {
        try {
            return $this->doFiscalize($invoiceData, $saleId);
        } catch (ClientException $e) {
            if ($e->getCode() === 401) {
                $this->authService->forceRefresh();
                return $this->doFiscalize($invoiceData, $saleId);
            }
            $body = $e->getResponse()->getBody()->getContents();
            throw new \RuntimeException("TaxCore fiscalization failed [{$e->getCode()}]: {$body}", $e->getCode(), $e);
        }
    }

    private function doFiscalize(array $invoiceData, string $saleId): TaxCoreInvoice
    {
        $client  = $this->certService->buildGuzzleClient();
        $baseUrl = $this->certService->getEndpoint();
        $path    = config('taxcore.paths.invoice', '/v3/invoices');

        // E-SDC does not require an Authorization header.
        // Ensure PIN was verified (throws if PIN verification fails).
        $this->authService->getToken();

        $response = $client->post($baseUrl . $path, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Accept-Language' => 'en-US',
            ],
            'json' => $invoiceData,
        ]);

        $data = json_decode($response->getBody()->getContents(), true) ?? [];

        return $this->invoiceRepository->store([
            'sale_id' => $saleId ?: null,
            'taxcore_uid' => $data['invoiceNumber']    ?? null,
            'qr_code' => $data['verificationQRCode'] ?? null,
            'digital_signature' => $data['signature']          ?? null,
            'verification_url' => $data['verificationUrl']    ?? null,
            'raw_response' => $data,
        ]);
    }

    /**
     * Build a TaxCore v3 invoice payload from a flat key→value array.
     *
     * Expected keys:
     *   invoiceType (optional, default "Normal"), transactionType (optional, default "Sale"),
     *   cashier (optional), dateAndTimeOfIssue (optional),
     *   buyerId (optional), buyerCostCenterId (optional),
     *   items => [ [name, quantity, unitPrice, labels (array), totalAmount (optional)], ... ]
     *   payment => [ [paymentType, amount], ... ]  (optional, defaults to Cash total)
     */
    public function buildPayload(array $input): array
    {
        $items = array_map(static function (array $item): array {
            $qty       = (float) ($item['quantity']  ?? 1);
            $unitPrice = (float) ($item['unitPrice'] ?? 0);
            $total     = isset($item['totalAmount'])
                ? (float) $item['totalAmount']
                : round($qty * $unitPrice, 2);

            return [
                'gtin' => $item['gtin']   ?? null,
                'name' => $item['name']   ?? '',
                'quantity' => $qty,
                'unitPrice' => round($unitPrice, 2),
                'labels' => $item['labels'] ?? [$item['taxCode'] ?? 'E'],
                'totalAmount' => round($total, 2),
            ];
        }, $input['items'] ?? []);

        $totalAmount = array_sum(array_column($items, 'totalAmount'));

        $payment = $input['payment'] ?? [[
            'paymentType' => 'Cash',
            'amount' => round($totalAmount, 2),
        ]];

        $payload = [
            'invoiceType' => $input['invoiceType']     ?? 'Normal',
            'transactionType' => $input['transactionType'] ?? 'Sale',
            'payment' => $payment,
            'items' => $items,
        ];

        if (!empty($input['cashier'])) {
            $payload['cashier'] = $input['cashier'];
        }
        if (!empty($input['dateAndTimeOfIssue'])) {
            $payload['dateAndTimeOfIssue'] = $input['dateAndTimeOfIssue'];
        }
        if (!empty($input['buyerId'])) {
            $payload['buyerId'] = $input['buyerId'];
        }
        if (!empty($input['buyerCostCenterId'])) {
            $payload['buyerCostCenterId'] = $input['buyerCostCenterId'];
        }
        if (!empty($input['invoiceNumber'])) {
            $payload['invoiceNumber'] = $input['invoiceNumber'];
        }
        if (!empty($input['referentDocumentNumber'])) {
            $payload['referentDocumentNumber'] = $input['referentDocumentNumber'];
        }

        return $payload;
    }
}
