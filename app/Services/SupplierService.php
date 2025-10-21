<?php

namespace App\Services;

use App\Models\Supplier;
use App\Repositories\SupplierRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
            throw new \RuntimeException('Supplier not found.');
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
        try {
            $supplier = $this->supplierRepository->find($id);
        } catch (ModelNotFoundException $e) {
            throw new \RuntimeException('Supplier not found.');
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
