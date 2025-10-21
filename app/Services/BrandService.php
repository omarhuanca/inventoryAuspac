<?php

namespace App\Services;

use App\Models\Brand;
use App\Repositories\BrandRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BrandService
{
    private BrandRepository $brandRepository;

    public function __construct(BrandRepository $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    public function getAllBrands()
    {
        return $this->brandRepository->getAll();
    }

    public function getBrandById($id)
    {
        $brand = $this->brandRepository->find($id);

        if (!$brand) {
            throw new \RuntimeException('Brand not found.');
        }

        return $brand;
    }

    public function createBrand(array $data)
    {
        if (Brand::whereRaw('LOWER(code) = ?', [strtolower($data['code'])])->exists()) {
            throw new \RuntimeException('Brand code already exists.');
        }

        return $this->brandRepository->create($data);
    }

    public function updateBrand(int $id, array $data)
    {
        try {
            $brand = $this->brandRepository->find($id);
        } catch (ModelNotFoundException $e) {
            throw new \RuntimeException('Brand not found.');
        }

        if (isset($data['code'])) {
            $exists = Brand::whereRaw('LOWER(code) = ?', [strtolower($data['code'])])
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                throw new \RuntimeException('Brand code already exists.');
            }
        }

        return $this->brandRepository->update($id, $data);
    }

}
