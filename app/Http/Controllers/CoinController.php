<?php

namespace App\Http\Controllers;

use App\Exceptions\NotFoundException;
use App\Http\Requests\StoreCoinRequest;
use App\Http\Requests\UpdateCoinRequest;
use App\Http\Resources\CoinResource;
use App\Http\Responses\ApiResponse;
use App\Services\CoinService;
use Illuminate\Http\Request;

class CoinController extends Controller
{
    private CoinService $coinService;

    public function __construct(CoinService $coinService)
    {
        $this->coinService = $coinService;
    }

    /**
     * @OA\Get(
     *     path="/api/coins",
     *     summary="Get all coins",
     *     tags={"Coins"},
     *     description="Return a list of all coins.",
     *     @OA\Response(
     *         response=200,
     *         description="Coin list.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Coin"))
     *         )
     *     ),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function index()
    {
        try {
            $coins = $this->coinService->getAllCoins();
            return ApiResponse::success('Coin list.', 200, CoinResource::collection($coins));
        } catch (\Exception $e) {
            return ApiResponse::error('Error obtaining the list of coins: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/coins",
     *     summary="Create a new coin",
     *     tags={"Coins"},
     *     description="Create a new coin.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Coin")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Coin created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Coin")
     *     ),
     *     @OA\Response(response=422, description="Validation failed")
     * )
     */
    public function store(StoreCoinRequest $request)
    {
        try {
            $coin = $this->coinService->createCoin($request->validated());
            return ApiResponse::success('Coin created.', 201, $coin);
        } catch (\RuntimeException $e) {
            return ApiResponse::error('Error when creating the coin: ' . $e->getMessage(), 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/coins/{id}",
     *     summary="Get a specific coin",
     *     tags={"Coins"},
     *     description="Return a specific coin by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the coin",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Coin found", @OA\JsonContent(ref="#/components/schemas/Coin")),
     *     @OA\Response(response=404, description="Coin not found"),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function show(string $id)
    {
        try {
            $coin = $this->coinService->getCoinById($id);
            return ApiResponse::success('Coin found', 200, new CoinResource($coin));
        } catch (NotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/coins/{id}",
     *     summary="Update a coin",
     *     tags={"Coins"},
     *     description="Update an existing coin by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the coin",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Coin")),
     *     @OA\Response(response=200, description="Coin updated successfully", @OA\JsonContent(ref="#/components/schemas/Coin")),
     *     @OA\Response(response=404, description="Coin not found"),
     *     @OA\Response(response=422, description="Validation failed"),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function update(UpdateCoinRequest $request, string $id)
    {
        try {
            $coin = $this->coinService->updateCoin($id, $request->validated());
            return ApiResponse::success('Coin updated.', 200, new CoinResource($coin));
        } catch (NotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\RuntimeException $e) {
            return ApiResponse::error('Error when updating the coin: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }
}
