<?php

namespace App\Services;

use App\Repositories\BundleProductRepository;
use Illuminate\Support\Facades\DB;

class BundleProductService
{
    private BundleProductRepository $bundleProductRepository;
    private BundleService $bundleService;

    public function __construct(BundleProductRepository $bundleProductRepository, BundleService $bundleService)
    {
        $this->bundleProductRepository = $bundleProductRepository;
        $this->bundleService = $bundleService;
    }

    public function deleteBundle(int $bundleId): void
    {
        $bundle = $this->bundleService->getBundleById($bundleId);

        DB::transaction(function() use ($bundleId, $bundle) {
            $this->bundleProductRepository->deleteByBundleId($bundleId);
            $bundle->delete();
        });
    }
}
