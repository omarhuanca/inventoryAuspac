<?php

namespace App\Modules\TaxCore\Service;

use App\Modules\TaxCore\Repository\TaxRateRepository;
use GuzzleHttp\Exception\ClientException;

class TaxCoreParameterService
{
    public function __construct(
        private TaxCoreCertificateService $certService,
        private TaxCoreAuthService        $authService,
        private TaxRateRepository         $taxRateRepository
    ) {}

    /**
     * Call Get Environment Parameters on the V-SDC and persist the returned tax rates.
     *
     * @return array The raw parameters returned by TaxCore.
     */
    public function syncRates(): array
    {
        $data = $this->fetchEnvironmentParameters();

        $rates = $this->extractRates($data);

        if (!empty($rates)) {
            $this->taxRateRepository->upsert($rates);
        }

        return $data;
    }

    /**
     * Perform the HTTP call to Get Environment Parameters.
     * Retries once if the server returns 401 (stale token).
     */
    private function fetchEnvironmentParameters(): array
    {
        try {
            return $this->doFetch();
        } catch (ClientException $e) {
            if ($e->getCode() === 401) {
                $this->authService->forceRefresh();
                return $this->doFetch();
            }
            $body = $e->getResponse()->getBody()->getContents();
            throw new \RuntimeException("TaxCore Get Environment Parameters failed [{$e->getCode()}]: {$body}", $e->getCode(), $e);
        }
    }

    private function doFetch(): array
    {
        $client  = $this->certService->buildGuzzleClient();
        $baseUrl = $this->certService->getEndpoint();
        $path    = config('taxcore.paths.env_params', '/vsdc/api/environmentInfo');

        // E-SDC does not require client authentication on this endpoint.
        // PIN was already verified via TaxCoreAuthService before this call.
        $response = $client->get($baseUrl . $path, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true) ?? [];
    }

    /**
     * Normalize the tax rates array from whatever structure TaxCore returns.
     * Common field names across TaxCore implementations are handled here.
     */
    private function extractRates(array $data): array
    {
        // TaxCore may nest rates under different keys depending on the jurisdiction
        $candidates = [
            $data['taxRates'] ?? [],
            $data['tax_rates'] ?? [],
            $data['TaxRates'] ?? [],
            $data['taxes'] ?? [],
            $data['vatRates'] ?? [],
        ];

        foreach ($candidates as $list) {
            if (!empty($list) && is_array($list)) {
                return $list;
            }
        }

        // If TaxCore returns a flat object like { "A": 15, "B": 0 }, normalise it
        $normalised = [];
        foreach ($data as $key => $value) {
            if (is_numeric($value)) {
                $normalised[] = ['code' => $key, 'name' => $key, 'rate' => $value];
            }
        }

        return $normalised;
    }
}
