<?php

namespace App\Http\Controllers;

use App\Exceptions\NotFoundException;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Http\Resources\BrandResource;
use App\Http\Responses\ApiResponse;
use App\Services\BrandService;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="Inventory Auspac Documentation",
 *     version="1.0.0",
 *     description="Project Inventory Auspac Laravel 12 Documentation",
 *     @OA\Contact(
 *         email="example@email.com"
 *     )
 * )
 */
class BrandController extends Controller
{
    private BrandService $brandService;

    public function __construct(BrandService $brandService)
    {
        $this->brandService = $brandService;
    }

    /**
     * @OA\Get(
     *     path="/api/brands",
     *     summary="Get all brands",
     *     tags={"Brands"},
     *     description="Return a list of all brands.",
     *     @OA\Response(
     *         response=200,
     *         description="Brand List.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Brand"))
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $brands = $this->brandService->getAllBrands();
            return ApiResponse::success('Brand list.', 200, BrandResource::collection($brands));
        } catch(\Exception $e) {
            return ApiResponse::error('Error obtaining the list of brands: '.$e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/brands",
     *     summary="Create a new brand",
     *     tags={"Brands"},
     *     description="Create a new brand.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Brand")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Brand created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Brand")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     )
     * )
     */
    public function store(StoreBrandRequest $request)
    {
        try {
            $brand = $this->brandService->createBrand($request->validated());
            return ApiResponse::success('Brand created.', 201, $brand);
        } catch(\RuntimeException $e) {
            return ApiResponse::error('Error when creating the service.: '.$e->getMessage(), 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/brands/{id}",
     *     summary="Get a specific brand",
     *     tags={"Brands"},
     *     description="Return a specific brand by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the brand",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Brand found",
     *         @OA\JsonContent(ref="#/components/schemas/Brand")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Brand not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        try {
            $brand = $this->brandService->getBrandById($id);
            return ApiResponse::success('Brand found', 200, new BrandResource($brand));
        } catch (NotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Error obtaining the brand: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/brands/{id}",
     *     summary="Update a brand",
     *     tags={"Brands"},
     *     description="Update an existing brand by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the brand",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Brand")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Brand updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Brand")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Brand not found"
     *     )
     * )
     */
    public function update(UpdateBrandRequest $request, string $id)
    {
        try {
            $brand = $this->brandService->updateBrand($id, $request->validated());
            return ApiResponse::success('Brand updated.', 200, new BrandResource($brand));
        } catch (NotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\RuntimeException $e) {
            return ApiResponse::error('Error when updating the brand: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }
}
