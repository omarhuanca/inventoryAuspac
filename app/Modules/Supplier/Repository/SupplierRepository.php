<?php

namespace App\Modules\Supplier\Repository;

use App\Modules\Supplier\Domain\Supplier;

class SupplierRepository
{
    private Supplier $supplier;

    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    public function getAll()
    {
        return $this->supplier->all();
    }

    public function find(int $id)
    {
        return $this->supplier->find($id);
    }

    public function create(array $data)
    {
        $supplier = Supplier::at($data['name']);
        $supplier->save();
        return $supplier;
    }

    public function update(int $id, array $data)
    {
        $supplier = $this->find($id);

        if (isset($data['name'])) {
            Supplier::at($data['name']);
            $supplier->name = $data['name'];
        }

        $supplier->save();
        return $supplier;
    }
}
