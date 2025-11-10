<?php

namespace App\Modules\BundleProduct\Repository;

use App\Modules\BundleProduct\Domain\BundleProduct;

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
