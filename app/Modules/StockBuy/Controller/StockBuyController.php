<?php

namespace App\Modules\StockBuy\Controller;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StockBuyRequest;
use App\Http\Resources\StockBuyResource;
use App\Http\Responses\ApiResponse;
use App\Modules\StockBuy\Service\StockBuyService;

class StockBuyController extends Controller
{
    private StockBuyService $stockBuyService;

    public function __construct(StockBuyService $stockBuyService)
    {
        $this->stockBuyService = $stockBuyService;
    }

    /**
     * @OA\Get(
     *     path="/api/stockbuys",
     *     summary="Get all stock buys",
     *     tags={"StockBuys"},
     *     description="Retrieve all stock buy records, including the related product information.",
     *     @OA\Response(
     *         response=200,
     *         description="List of stock buys retrieved successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/StockBuy"))
     *         )
     *     ),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function index()
    {
        try {
            $stockBuys = $this->stockBuyService->getAllStockBuys();
            return ApiResponse::success('StockBuy list.', 200, StockBuyResource::collection($stockBuys));
        } catch (\Exception $e) {
            return ApiResponse::error('Error obtaining the list of StockBuys: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/stockbuys",
     *     summary="Create a new stock buy",
     *     tags={"StockBuys"},
     *     description="Registers a new stock purchase, increasing the stock quantity for a specific product.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product", "amount", "date", "description"},
     *             @OA\Property(property="product", ref="#/components/schemas/Product"),
     *             @OA\Property(property="amount", type="integer", example=10, description="Quantity of units purchased."),
     *             @OA\Property(property="date", type="string", format="date", example="2025-11-05", description="Purchase date."),
     *             @OA\Property(property="description", type="string", example="Purchase #001", description="Short description of the purchase.")
     *         )
     *     ),
     *     @OA\Response(response=201, description="StockBuy created successfully.", @OA\JsonContent(ref="#/components/schemas/StockBuy")),
     *     @OA\Response(response=422, description="Validation failed"),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function store(StockBuyRequest $request)
    {
        try {
            $stockBuy = $this->stockBuyService->createStockBuy($request->validated());
            return ApiResponse::success('StockBuy created.', 201, new StockBuyResource($stockBuy));
        } catch (\RuntimeException $e) {
            return ApiResponse::error('Error creating StockBuy: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/stockbuys/{id}",
     *     summary="Get a specific stock buy",
     *     tags={"StockBuys"},
     *     description="Retrieve a single stock buy record by its ID, including the associated product details.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the stock buy record",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="StockBuy found", @OA\JsonContent(ref="#/components/schemas/StockBuy")),
     *     @OA\Response(response=404, description="StockBuy not found"),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function show(string $id)
    {
        try {
            $stockBuy = $this->stockBuyService->getStockBuyById($id);
            return ApiResponse::success('StockBuy found.', 200, new StockBuyResource($stockBuy));
        } catch (NotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/stockbuys/{id}",
     *     summary="Update a stock buy",
     *     tags={"StockBuys"},
     *     description="Update an existing stock buy by its ID, adjusting the stock levels of the associated product accordingly.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the stock buy record to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StockBuy")
     *     ),
     *     @OA\Response(response=200, description="StockBuy updated successfully.", @OA\JsonContent(ref="#/components/schemas/StockBuy")),
     *     @OA\Response(response=404, description="StockBuy not found"),
     *     @OA\Response(response=422, description="Validation failed"),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function update(StockBuyRequest $request, string $id)
    {
        try {
            $stockBuy = $this->stockBuyService->updateStockBuy($id, $request->validated());
            return ApiResponse::success('StockBuy updated.', 200, new StockBuyResource($stockBuy));
        } catch (NotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\RuntimeException $e) {
            return ApiResponse::error('Error updating StockBuy: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }
}
