<?php

namespace App\Repositories;

use App\Models\Brand;

class BrandRepository
{
    protected Brand $brand;

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
        return $this->brand->findOrFail($id);
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
}
