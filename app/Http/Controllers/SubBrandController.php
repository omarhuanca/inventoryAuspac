<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubBrandRequest;
use App\Http\Requests\UpdateSubBrandRequest;
use App\Http\Resources\SubBrandResource;
use App\Http\Responses\ApiResponse;
use App\Services\SubBrandService;
use Illuminate\Http\Request;

class SubBrandController extends Controller
{
    private SubBrandService $subBrandService;

    public function __construct(SubBrandService $subBrandService)
    {
        $this->subBrandService = $subBrandService;
    }
    public function index()
    {
        try {
            $subBrands = $this->subBrandService->getAllSubBrands();
            return ApiResponse::success('SubBrand list.', 200, SubBrandResource::collection($subBrands));
        } catch (\Exception $e) {
            return ApiResponse::error('Error obtaining the list of SubBrands: '.$e->getMessage(), 500);
        }
    }

    public function store(StoreSubBrandRequest $request)
    {
        try {
            $subBrand = $this->subBrandService->createSubBrand($request->validated());
            return ApiResponse::success('SubBrand created.', 201, new SubBrandResource($subBrand));
        } catch (\RuntimeException $e) {
            return ApiResponse::error('Error creating SubBrand: ' . $e->getMessage(), 422);
        }
    }

    public function show(string $id)
    {
        try {
            $subBrand = $this->subBrandService->getSubBrandById($id);
            return ApiResponse::success('SubBrand found', 200, new SubBrandResource($subBrand));
        } catch (\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }

    public function update(UpdateSubBrandRequest $request, string $id)
    {
        try {
            $subBrand = $this->subBrandService->updateSubBrand($id, $request->validated());
            return ApiResponse::success('SubBrand updated.', 200, new SubBrandResource($subBrand));
        } catch (\RuntimeException $e) {
            return ApiResponse::error('Error updating SubBrand: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }
}
