<?php

namespace App\Modules\SubBrand\Service;

use App\Exceptions\NotFoundException;
use App\Modules\Brand\Service\BrandService;
use App\Modules\SubBrand\Domain\SubBrand;
use App\Modules\SubBrand\Repository\SubBrandRepository;

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
        $subBrand = $this->subBrandRepository->find($id);

        if (!$subBrand) {
            throw new NotFoundException('SubBrand not found.');
        }

        return $subBrand;
    }

    public function createSubBrand(array $data)
    {
        $brand = $this->brandService->getBrandById($data['brand']['id']);

        $exists = SubBrand::whereRaw('LOWER(code) = ?', [strtolower($data['code'])])->exists();

        if ($exists) {
            throw new \RuntimeException('SubBrand code already exists.');
        }

        return $this->subBrandRepository->create($data, $brand);
    }

    public function updateSubBrand(int $id, array $data)
    {
        $subBrand = $this->getSubBrandById($id);

        $brand = $this->brandService->getBrandById($data['brand']['id']);

        if (isset($data['code'])) {
            $exists = SubBrand::whereRaw('LOWER(code) = ?', [strtolower($data['code'])])
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                throw new \RuntimeException('SubBrand code already exists.');
            }
        }

        return $this->subBrandRepository->update($subBrand, $data, $brand);
    }

    public function deleteSubBrand(int $id): void
    {
        $subBrand = $this->subBrandRepository->find($id);

        if (!$subBrand) {
            throw new NotFoundException('SubBrand not found.');
        }

        $this->subBrandRepository->delete($id);
    }
}
