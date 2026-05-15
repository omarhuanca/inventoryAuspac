<?php

namespace App\Modules\SubBrand\Repository;

use App\Modules\Brand\Domain\Brand;
use App\Modules\SubBrand\Domain\SubBrand;

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
        return $this->subBrand->with('brand')->find($id);
    }

    public function create(array $data, Brand $brand)
    {
        $subBrand = $this->subBrand::at($data['code'], $brand);
        $subBrand->save();
        return $subBrand->load('brand');
    }

    public function update(SubBrand $subBrand, array $data, Brand $brand)
    {
        if (isset($data['code'])) {
            $this->subBrand::at($data['code'], $brand);
            $subBrand->code = trim($data['code']);
        }

        $subBrand->brand_id = $brand->id;
        $subBrand->save();
        return $subBrand->load('brand');
    }

    public function delete(int $id): void
    {
        $subBrand = $this->find($id);
        $subBrand->delete();
    }
}
