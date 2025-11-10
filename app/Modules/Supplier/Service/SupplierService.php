<?php

namespace App\Modules\Supplier\Service;

use App\Exceptions\NotFoundException;
use App\Modules\Supplier\Domain\Supplier;
use App\Modules\Supplier\Repository\SupplierRepository;

class SupplierService
{
    private SupplierRepository $supplierRepository;

    public function __construct(SupplierRepository $supplierRepository)
    {
        $this->supplierRepository = $supplierRepository;
    }

    public function getAllSuppliers()
    {
        return $this->supplierRepository->getAll();
    }

    public function getSupplierById(int $id)
    {
        $supplier = $this->supplierRepository->find($id);

        if (!$supplier) {
            throw new NotFoundException('Supplier not found.');
        }

        return $supplier;
    }

    public function createSupplier(array $data)
    {
        if (Supplier::whereRaw('LOWER(name) = ?', [strtolower($data['name'])])->exists()) {
            throw new \RuntimeException('Supplier name already exists.');
        }

        return $this->supplierRepository->create($data);
    }

    public function updateSupplier(int $id, array $data)
    {
        $supplier = $this->supplierRepository->find($id);

        if (!$supplier) {
            throw new NotFoundException('Supplier not found.');
        }

        if (isset($data['name'])) {
            $exists = Supplier::whereRaw('LOWER(name) = ?', [strtolower($data['name'])])
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                throw new \RuntimeException('Supplier name already exists.');
            }
        }

        return $this->supplierRepository->update($id, $data);
    }
}
