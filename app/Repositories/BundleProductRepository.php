<?php

namespace App\Repositories;

use App\Models\BundleProduct;
use Illuminate\Support\Facades\DB;

class BundleProductRepository
{
    private BundleProduct $bundleProduct;

    public function __construct(BundleProduct $bundleProduct)
    {
        $this->bundleProduct = $bundleProduct;
    }

    public function deleteByBundleId(int $bundleId): void
    {
        $this->bundleProduct->where('bundle_id', $bundleId)->delete();
    }
}
