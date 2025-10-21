<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Http\Responses\ApiResponse;
use App\Services\SupplierService;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    private SupplierService $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    public function index()
    {
        try {
            $suppliers = $this->supplierService->getAllSuppliers();
            return ApiResponse::success('Supplier list.', 200, SupplierResource::collection($suppliers));
        } catch (\Exception $e) {
            return ApiResponse::error('Error obtaining supplier list: ' . $e->getMessage(), 500);
        }
    }

    public function store(StoreSupplierRequest $request)
    {
        try {
            $supplier = $this->supplierService->createSupplier($request->validated());
            return ApiResponse::success('Supplier created.', 201, new SupplierResource($supplier));
        } catch (\RuntimeException $e) {
            return ApiResponse::error('Error creating supplier: ' . $e->getMessage(), 422);
        }
    }

    public function show(string $id)
    {
        try {
            $supplier = $this->supplierService->getSupplierById($id);
            return ApiResponse::success('Supplier found.', 200, new SupplierResource($supplier));
        } catch (\Exception $e) {
            return ApiResponse::error('Error obtaining supplier: ' . $e->getMessage(), 500);
        }
    }

    public function update(UpdateSupplierRequest $request, string $id)
    {
        try {
            $supplier = $this->supplierService->updateSupplier($id, $request->validated());
            return ApiResponse::success('Supplier updated.', 200, new SupplierResource($supplier));
        } catch (\RuntimeException $e) {
            return ApiResponse::error('Error updating supplier: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }
}
