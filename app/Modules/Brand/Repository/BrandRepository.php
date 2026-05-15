<?php

namespace App\Modules\Brand\Repository;

use App\Modules\Brand\Domain\Brand;

class BrandRepository
{
    private Brand $brand;

    public function __construct(Brand $brand)
    {
        $this->brand = $brand;
    }
    public function getAll()
    {
        return $this->brand->all();
    }

    public function find(int $id)
    {
        return $this->brand->find($id);
    }

    public function create(array $data)
    {
        $brand = $this->brand::at($data['code']);
        $brand->save();
        return $brand;
    }

    public function update($id, array $data)
    {
        $brand = $this->find($id);

        if (isset($data['code'])) {
            Brand::at($data['code']);
            $brand->code = $data['code'];
        }

        $brand->save();
        return $brand;
    }

    public function delete(int $id): void
    {
        $brand = $this->find($id);
        $brand->delete();
    }
}
