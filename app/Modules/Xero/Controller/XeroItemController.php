<?php

namespace App\Modules\Xero\Controller;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Http\Responses\ApiResponse;
use App\Modules\Product\Service\ProductService;
use App\Modules\Xero\Service\XeroItemService;
use XeroAPI\XeroPHP\ApiException;

class XeroItemController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private XeroItemService $xeroItemService,
    ) {}

    /**
     * @OA\Post(
     *     path="/api/xero/items",
     *     summary="Create product locally and sync to Xero",
     *     tags={"Xero"},
     *     description="Creates a product in the local database and then pushes it to Xero as an Item. If Xero sync fails, the product remains saved locally.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Product data to create and sync",
     *         @OA\JsonContent(ref="#/components/schemas/Product")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created and synced successfully to Xero",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Product created and synced to Xero."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Product"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed or product creation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Error creating product: Validation failed."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=503,
     *         description="Product saved locally but could not sync to Xero because no valid Xero token exists",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Product saved locally but could not sync to Xero: No valid Xero token found."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Product"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=502,
     *         description="Product saved locally but Xero API sync failed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Product saved locally but Xero sync failed: Xero API returned an error."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/Product"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unexpected server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unexpected error: Internal server error."
     *             )
     *         )
     *     )
     * )
     *
     * Create a product locally and push it to Xero as an Item.
     *
     * POST /api/xero/items
     */
    public function store(ProductRequest $request)
    {
        try {
            $product = $this->productService->createProduct($request->validated());
        } catch (\RuntimeException $e) {
            return ApiResponse::error('Error creating product: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }

        try {
            $xeroItemId = $this->xeroItemService->pushProduct($product);
            $product->update(['xero_item_id' => $xeroItemId]);
            $product->refresh()->load([
                'supplierCoin',
                'landingCoin',
                'measure',
                'subBrand.brand',
                'supplier'
            ]);
        } catch (\RuntimeException $e) {
            return ApiResponse::error('Product saved locally but could not sync to Xero: ' . $e->getMessage(), 503, new ProductResource($product));
        } catch (ApiException $e) {
            return ApiResponse::error('Product saved locally but Xero sync failed: ' . $e->getMessage(), 502, new ProductResource($product));
        } catch (\Exception $e) {
            return ApiResponse::error('Product saved locally but Xero sync failed: ' . $e->getMessage(), 502, new ProductResource($product));
        }

        return ApiResponse::success('Product created and synced to Xero.', 201, new ProductResource($product));
    }
}