<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Http\Resources\BrandResource;
use App\Http\Responses\ApiResponse;
use App\Services\BrandService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    private BrandService $brandService;

    public function __construct(BrandService $brandService)
    {
        $this->brandService = $brandService;
    }

    public function index()
    {
        try {
            $brands = $this->brandService->getAllBrands();
            return ApiResponse::success('Brand list.', 200, BrandResource::collection($brands));
        } catch(\Exception $e) {
            return ApiResponse::error('Error obtaining the list of brands: '.$e->getMessage(), 500);
        }
    }

    public function store(StoreBrandRequest $request)
    {
        try {
            $brand = $this->brandService->createBrand($request->validated());
            return ApiResponse::success('Brand created.', 201, $brand);
        } catch(\RuntimeException $e) {
            return ApiResponse::error('Error when creating the service.: '.$e->getMessage(), 422);
        }
    }

    public function show(string $id)
    {
        try {
            $brand = $this->brandService->getBrandById($id);
            return ApiResponse::success('Brand found', 200, new BrandResource($brand));
        } catch (\Exception $e) {
            return ApiResponse::error('Error obtaining the brand: ' . $e->getMessage(), 500);
        }
    }

    public function update(UpdateBrandRequest $request, string $id)
    {
        try {
            $brand = $this->brandService->updateBrand($id, $request->validated());
            return ApiResponse::success('Brand updated.', 200, new BrandResource($brand));
        } catch (\RuntimeException $e) {
            return ApiResponse::error('Error when updating the brand: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }
}
