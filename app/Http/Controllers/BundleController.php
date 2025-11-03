<?php

namespace App\Http\Controllers;

use App\Exceptions\NotFoundException;
use App\Http\Requests\BundleRequest;
use App\Http\Resources\BundleResource;
use App\Http\Responses\ApiResponse;
use App\Services\BundleService;
use Illuminate\Http\Request;

class BundleController extends Controller
{
    private BundleService $bundleService;

    public function __construct(BundleService $bundleService)
    {
        $this->bundleService = $bundleService;
    }

    /**
     * @OA\Get(
     *     path="/api/bundles",
     *     summary="Get all bundles",
     *     tags={"Bundles"},
     *     description="Retrieve all bundles, including their products and pricing information.",
     *     @OA\Response(
     *         response=200,
     *         description="Bundle list retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Bundle"))
     *         )
     *     ),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function index()
    {
        try {
            $bundles = $this->bundleService->getAllBundles();
            return ApiResponse::success('Bundle list.', 200, BundleResource::collection($bundles));
        } catch (\Exception $e) {
            return ApiResponse::error('Error obtaining the list of bundles: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/bundles",
     *     summary="Create a new bundle",
     *     tags={"Bundles"},
     *     description="Create a new bundle with its pricing, coin, and associated products. A bundle must contain at least two products.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code", "landing_cost_price", "landing_coin", "retail_price", "promotional_price", "products"},
     *             @OA\Property(property="code", type="string", example="B001"),
     *             @OA\Property(property="landing_cost_price", type="number", format="float", example=300.00),
     *             @OA\Property(property="landing_coin", ref="#/components/schemas/Coin"),
     *             @OA\Property(property="retail_price", type="number", format="float", example=400.00),
     *             @OA\Property(property="promotional_price", type="number", format="float", example=350.00),
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 description="Products that make up this bundle",
     *                 @OA\Items(ref="#/components/schemas/Product")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Bundle created successfully.", @OA\JsonContent(ref="#/components/schemas/Bundle")),
     *     @OA\Response(response=422, description="Validation failed"),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function store(BundleRequest $request)
    {
        try {
            $bundle = $this->bundleService->createBundle($request->validated());
            return ApiResponse::success('Bundle created.', 201, new BundleResource($bundle));
        } catch (\RuntimeException $e) {
            return ApiResponse::error('Error creating Bundle: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/bundles/{id}",
     *     summary="Get a specific bundle",
     *     tags={"Bundles"},
     *     description="Retrieve a bundle by its ID, including its associated products.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the bundle",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Bundle found", @OA\JsonContent(ref="#/components/schemas/Bundle")),
     *     @OA\Response(response=404, description="Bundle not found"),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function show(string $id)
    {
        try {
            $bundle = $this->bundleService->getBundleById($id);
            return ApiResponse::success('Bundle found.', 200, new BundleResource($bundle));
        } catch (NotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/bundles/{id}",
     *     summary="Update a bundle",
     *     tags={"Bundles"},
     *     description="Update an existing bundle by its ID, validating all constraints and product associations.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the bundle to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Bundle")
     *     ),
     *     @OA\Response(response=200, description="Bundle updated successfully.", @OA\JsonContent(ref="#/components/schemas/Bundle")),
     *     @OA\Response(response=404, description="Bundle not found"),
     *     @OA\Response(response=422, description="Validation failed"),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function update(BundleRequest $request, string $id)
    {
        try {
            $bundle = $this->bundleService->updateBundle($id, $request->validated());
            return ApiResponse::success('Bundle updated.', 200, new BundleResource($bundle));
        } catch (NotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\RuntimeException $e) {
            return ApiResponse::error('Error updating Bundle: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }
}
