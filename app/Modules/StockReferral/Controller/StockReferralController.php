<?php

namespace App\Modules\StockReferral\Controller;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StockReferralRequest;
use App\Http\Resources\StockReferralResource;
use App\Http\Responses\ApiResponse;
use App\Modules\StockReferral\Service\StockReferralService;
use Illuminate\Http\Request;

class StockReferralController extends Controller
{
    private StockReferralService $stockReferralService;

    public function __construct(StockReferralService $stockReferralService)
    {
        $this->stockReferralService = $stockReferralService;
    }

    /**
     * @OA\Get(
     *     path="/api/stockreferrals",
     *     summary="Get all stock referrals",
     *     tags={"StockReferrals"},
     *     description="Retrieve all stock referral records, including the related product information.",
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of stock referrals retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/StockReferral")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function index()
    {
        try {
            $referrals = $this->stockReferralService->getAllStockReferrals();
            return ApiResponse::success('StockReferral list.', 200, StockReferralResource::collection($referrals));
        } catch (\Exception $e) {
            return ApiResponse::error('Error obtaining StockReferrals: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/stockreferrals",
     *     summary="Create a new stock referral",
     *     tags={"StockReferrals"},
     *     description="Registers a new stock referral, decreasing the stock quantity for a specific product.",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product", "amount", "date"},
     *             @OA\Property(property="product", ref="#/components/schemas/Product"),
     *             @OA\Property(property="amount", type="integer", example=5, description="Quantity removed from stock."),
     *             @OA\Property(property="date", type="string", format="date", example="2025-11-05", description="Referral date.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="StockReferral created successfully.",
     *         @OA\JsonContent(ref="#/components/schemas/StockReferral")
     *     ),
     *
     *     @OA\Response(response=422, description="Validation failed"),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function store(StockReferralRequest $request)
    {
        try {
            $referral = $this->stockReferralService->createStockReferral($request->validated());
            return ApiResponse::success('StockReferral created.', 201, new StockReferralResource($referral));
        } catch (\RuntimeException $e) {
            return ApiResponse::error('Error creating StockReferral: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/stockreferrals/{id}",
     *     summary="Get a specific stock referral",
     *     tags={"StockReferrals"},
     *     description="Retrieve a single stock referral record by its ID, including the associated product details.",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the stock referral record",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="StockReferral found",
     *         @OA\JsonContent(ref="#/components/schemas/StockReferral")
     *     ),
     *
     *     @OA\Response(response=404, description="StockReferral not found"),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function show(string $id)
    {
        try {
            $referral = $this->stockReferralService->getStockReferralById($id);
            return ApiResponse::success('StockReferral found.', 200, new StockReferralResource($referral));
        } catch (NotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/stockreferrals/{id}",
     *     summary="Update a stock referral",
     *     tags={"StockReferrals"},
     *     description="Update an existing stock referral by its ID, adjusting the stock levels of the associated product accordingly.",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the stock referral record to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StockReferral")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="StockReferral updated successfully.",
     *         @OA\JsonContent(ref="#/components/schemas/StockReferral")
     *     ),
     *
     *     @OA\Response(response=404, description="StockReferral not found"),
     *     @OA\Response(response=422, description="Validation failed"),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function update(StockReferralRequest $request, string $id)
    {
        try {
            $referral = $this->stockReferralService->updateStockReferral($id, $request->validated());
            return ApiResponse::success('StockReferral updated.', 200, new StockReferralResource($referral));
        } catch (NotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\RuntimeException $e) {
            return ApiResponse::error('Error updating StockReferral: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/stock-referrals/{id}",
     *     summary="Delete a stock referral",
     *     tags={"StockReferrals"},
     *     description="Delete an existing stock referral. This will reverse the stock decrease applied when the referral was created.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the stock referral",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="StockReferral deleted successfully"),
     *     @OA\Response(response=404, description="StockReferral not found"),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function destroy(string $id)
    {
        try {
            $this->stockReferralService->deleteStockReferral((int) $id);
            return ApiResponse::success('StockReferral deleted.', 200);
        } catch (NotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }
}
