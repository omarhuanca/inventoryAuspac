<?php

namespace App\Modules\SubBrand\Controller;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubBrandRequest;
use App\Http\Resources\SubBrandResource;
use App\Http\Responses\ApiResponse;
use App\Modules\SubBrand\Service\SubBrandService;

class SubBrandController extends Controller
{
    private SubBrandService $subBrandService;

    public function __construct(SubBrandService $subBrandService)
    {
        $this->subBrandService = $subBrandService;
    }

    /**
     * @OA\Get(
     *     path="/api/subbrands",
     *     summary="Get all sub-brands",
     *     tags={"SubBrands"},
     *     description="Return a list of all sub-brands.",
     *     @OA\Response(
     *         response=200,
     *         description="SubBrand List.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SubBrand"))
     *         )
     *     ),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function index()
    {
        try {
            $subBrands = $this->subBrandService->getAllSubBrands();
            return ApiResponse::success('SubBrand list.', 200, SubBrandResource::collection($subBrands));
        } catch (\Exception $e) {
            return ApiResponse::error('Error obtaining the list of SubBrands: '.$e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/subbrands",
     *     summary="Create a new sub-brand",
     *     tags={"SubBrands"},
     *     description="Create a new sub-brand associated with a brand.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SubBrand")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="SubBrand created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/SubBrand")
     *     ),
     *     @OA\Response(response=422, description="Validation failed")
     * )
     */
    public function store(SubBrandRequest $request)
    {
        try {
            $subBrand = $this->subBrandService->createSubBrand($request->validated());
            return ApiResponse::success('SubBrand created.', 201, new SubBrandResource($subBrand));
        } catch (\RuntimeException $e) {
            return ApiResponse::error('Error creating SubBrand: ' . $e->getMessage(), 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/subbrands/{id}",
     *     summary="Get a specific sub-brand",
     *     tags={"SubBrands"},
     *     description="Return a specific sub-brand by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the sub-brand",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="SubBrand found", @OA\JsonContent(ref="#/components/schemas/SubBrand")),
     *     @OA\Response(response=404, description="SubBrand not found"),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function show(string $id)
    {
        try {
            $subBrand = $this->subBrandService->getSubBrandById($id);
            return ApiResponse::success('SubBrand found', 200, new SubBrandResource($subBrand));
        } catch (NotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/subbrands/{id}",
     *     summary="Update a sub-brand",
     *     tags={"SubBrands"},
     *     description="Update an existing sub-brand by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the sub-brand",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SubBrand")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SubBrand updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/SubBrand")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="SubBrand not found"
     *     ),
     *     @OA\Response(response=422, description="Validation failed"),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function update(SubBrandRequest $request, string $id)
    {
        try {
            $subBrand = $this->subBrandService->updateSubBrand($id, $request->validated());
            return ApiResponse::success('SubBrand updated.', 200, new SubBrandResource($subBrand));
        } catch (NotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\RuntimeException $e) {
            return ApiResponse::error('Error updating SubBrand: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/sub-brands/{id}",
     *     summary="Delete a sub-brand",
     *     tags={"SubBrands"},
     *     description="Delete an existing sub-brand by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the sub-brand",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="SubBrand deleted successfully"),
     *     @OA\Response(response=404, description="SubBrand not found"),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function destroy(string $id)
    {
        try {
            $this->subBrandService->deleteSubBrand((int) $id);
            return ApiResponse::success('SubBrand deleted.', 200);
        } catch (NotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }
}
