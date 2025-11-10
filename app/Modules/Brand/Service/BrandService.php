<?php

namespace App\Modules\Brand\Service;

use App\Exceptions\NotFoundException;
use App\Modules\Brand\Domain\Brand;
use App\Modules\Brand\Repository\BrandRepository;

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
            throw new NotFoundException('Brand not found.');
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
        $brand = $this->brandRepository->find($id);

        if (!$brand) {
            throw new NotFoundException('Brand not found.');
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
