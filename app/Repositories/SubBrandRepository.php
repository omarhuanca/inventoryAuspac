<?php

namespace App\Repositories;

use App\Models\Brand;
use App\Models\SubBrand;

class SubBrandRepository
{
    private SubBrand $subBrand;

    public function __construct(SubBrand $subBrand)
    {
        $this->subBrand = $subBrand;
    }

    public function getAll()
    {
        return $this->subBrand->with('brand')->get();
    }

    public function find(int $id)
    {
        return $this->subBrand->with('brand')->findOrFail($id);
    }

    public function create(array $data, Brand $brand)
    {
        $subBrand = $this->subBrand::at($data['code'], $brand);
        $subBrand->save();
        return $subBrand->load('brand');
    }

    public function update(SubBrand $subBrand, array $data, ?Brand $brand = null)
    {
        $brandToUse = $brand ?? $subBrand->brand;

        if (isset($data['code'])) {
            $this->subBrand::at($data['code'], $brandToUse);
            $subBrand->code = $data['code'];
        }

        if ($brand) {
            $subBrand->brand_id = $brand->id;
        }

        $subBrand->save();
        return $subBrand->load('brand');
    }
}
