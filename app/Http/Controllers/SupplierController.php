<?php

namespace App\Http\Controllers;

use App\Exceptions\NotFoundException;
use App\Http\Requests\SupplierRequest;
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

    /**
     * @OA\Get(
     *     path="/api/suppliers",
     *     summary="Get all suppliers",
     *     tags={"Suppliers"},
     *     description="Return a list of all suppliers.",
     *     @OA\Response(
     *         response=200,
     *         description="Supplier List.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Supplier"))
     *         )
     *     ),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function index()
    {
        try {
            $suppliers = $this->supplierService->getAllSuppliers();
            return ApiResponse::success('Supplier list.', 200, SupplierResource::collection($suppliers));
        } catch (\Exception $e) {
            return ApiResponse::error('Error obtaining supplier list: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/suppliers",
     *     summary="Create a new supplier",
     *     tags={"Suppliers"},
     *     description="Create a new supplier.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Supplier")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Supplier created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Supplier")
     *     ),
     *     @OA\Response(response=422, description="Validation failed")
     * )
     */
    public function store(SupplierRequest $request)
    {
        try {
            $supplier = $this->supplierService->createSupplier($request->validated());
            return ApiResponse::success('Supplier created.', 201, new SupplierResource($supplier));
        } catch (\RuntimeException $e) {
            return ApiResponse::error('Error creating supplier: ' . $e->getMessage(), 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/suppliers/{id}",
     *     summary="Get a specific supplier",
     *     tags={"Suppliers"},
     *     description="Return a specific supplier by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the supplier",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Supplier found",
     *         @OA\JsonContent(ref="#/components/schemas/Supplier")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Supplier not found"
     *     ),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function show(string $id)
    {
        try {
            $supplier = $this->supplierService->getSupplierById($id);
            return ApiResponse::success('Supplier found.', 200, new SupplierResource($supplier));
        } catch (NotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Error obtaining supplier: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/suppliers/{id}",
     *     summary="Update a supplier",
     *     tags={"Suppliers"},
     *     description="Update an existing supplier by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the supplier",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Supplier")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Supplier updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Supplier")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Supplier not found"
     *     ),
     *     @OA\Response(response=422, description="Validation failed"),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function update(SupplierRequest $request, string $id)
    {
        try {
            $supplier = $this->supplierService->updateSupplier($id, $request->validated());
            return ApiResponse::success('Supplier updated.', 200, new SupplierResource($supplier));
        } catch (NotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\RuntimeException $e) {
            return ApiResponse::error('Error updating supplier: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }
}
