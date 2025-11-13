<?php

namespace App\Modules\Measure\Controller;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\MeasureRequest;
use App\Http\Resources\MeasureResource;
use App\Http\Responses\ApiResponse;
use App\Modules\Measure\Service\MeasureService;

class MeasureController extends Controller
{
    private MeasureService $measureService;

    public function __construct(MeasureService $measureService)
    {
        $this->measureService = $measureService;
    }

    /**
     * @OA\Get(
     *     path="/api/measures",
     *     summary="Get all measures",
     *     tags={"Measures"},
     *     description="Return a list of all measures.",
     *     @OA\Response(
     *         response=200,
     *         description="Measure list.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Measure"))
     *         )
     *     ),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function index()
    {
        try {
            $measures = $this->measureService->getAllMeasures();
            return ApiResponse::success('Measure list.', 200, MeasureResource::collection($measures));
        } catch (\Exception $e) {
            return ApiResponse::error('Error obtaining the list of measures: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/measures",
     *     summary="Create a new measure",
     *     tags={"Measures"},
     *     description="Create a new measure.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Measure")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Measure created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Measure")
     *     ),
     *     @OA\Response(response=422, description="Validation failed")
     * )
     */
    public function store(MeasureRequest $request)
    {
        try {
            $measure = $this->measureService->createMeasure($request->validated());
            return ApiResponse::success('Measure created.', 201, new MeasureResource($measure));
        } catch (\RuntimeException $e) {
            return ApiResponse::error('Error when creating the measure: ' . $e->getMessage(), 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/measures/{id}",
     *     summary="Get a specific measure",
     *     tags={"Measures"},
     *     description="Return a specific measure by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the measure",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Measure found", @OA\JsonContent(ref="#/components/schemas/Measure")),
     *     @OA\Response(response=404, description="Measure not found"),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function show(string $id)
    {
        try {
            $measure = $this->measureService->getMeasureById($id);
            return ApiResponse::success('Measure found', 200, new MeasureResource($measure));
        } catch (NotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Error obtaining the measure: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/measures/{id}",
     *     summary="Update a measure",
     *     tags={"Measures"},
     *     description="Update an existing measure by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the measure",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Measure")),
     *     @OA\Response(response=200, description="Measure updated successfully", @OA\JsonContent(ref="#/components/schemas/Measure")),
     *     @OA\Response(response=404, description="Measure not found"),
     *     @OA\Response(response=422, description="Validation failed"),
     *     @OA\Response(response=500, description="Unexpected server error")
     * )
     */
    public function update(MeasureRequest $request, string $id)
    {
        try {
            $measure = $this->measureService->updateMeasure($id, $request->validated());
            return ApiResponse::success('Measure updated.', 200, new MeasureResource($measure));
        } catch (NotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\RuntimeException $e) {
            return ApiResponse::error('Error when updating the measure: ' . $e->getMessage(), 422);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }
}
