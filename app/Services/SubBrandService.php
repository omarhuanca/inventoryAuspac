<?php

namespace App\Services;

use App\Models\SubBrand;
use App\Repositories\SubBrandRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SubBrandService
{
    private SubBrandRepository $subBrandRepository;
    private BrandService $brandService;
    public function __construct(SubBrandRepository $subBrandRepository, BrandService $brandService)
    {
        $this->subBrandRepository = $subBrandRepository;
        $this->brandService = $brandService;
    }

    public function getAllSubBrands()
    {
        return $this->subBrandRepository->getAll();
    }

    public function getSubBrandById(int $id)
    {
        try {
            return $this->subBrandRepository->find($id);
        } catch (ModelNotFoundException $e) {
            throw new \RuntimeException('SubBrand not found.');
        }
    }

    public function createSubBrand(array $data)
    {
        $brand = $this->brandService->getBrandById($data['brand']['id']);

        $exists = SubBrand::where('brand_id', $brand->id)
            ->whereRaw('LOWER(code) = ?', [strtolower($data['code'])])
            ->exists();

        if ($exists) {
            throw new \RuntimeException('SubBrand code already exists for this Brand.');
        }

        return $this->subBrandRepository->create($data, $brand);
    }

    public function updateSubBrand(int $id, array $data)
    {
        $subBrand = $this->getSubBrandById($id);

        $brand = null;
        if (isset($data['brand'])) {
            $brand = $this->brandService->getBrandById($data['brand']['id']);

        }

        if (isset($data['code'])) {
            $brandToUse = $brand ?? $subBrand->brand;
            $exists = SubBrand::where('brand_id', $brandToUse->id)
                ->whereRaw('LOWER(code) = ?', [strtolower($data['code'])])
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                throw new \RuntimeException('SubBrand code already exists for this Brand.');
            }
        }

        return $this->subBrandRepository->update($subBrand, $data, $brand);
    }
}
