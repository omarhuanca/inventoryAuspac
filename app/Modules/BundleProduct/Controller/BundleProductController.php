<?php

namespace App\Modules\BundleProduct\Controller;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Modules\BundleProduct\Service\BundleProductService;

class BundleProductController extends Controller
{
    private BundleProductService $bundleProductService;

    public function __construct(BundleProductService $bundleProductService)
    {
        $this->bundleProductService = $bundleProductService;
    }

    /**
     * @OA\Delete(
     *     path="/api/bundleproducts/{bundleId}",
     *     summary="Delete a bundle",
     *     tags={"BundleProducts"},
     *     description="Deletes a bundle by its ID and also removes its associated products.",
     *     @OA\Parameter(
     *         name="bundleId",
     *         in="path",
     *         required=true,
     *         description="ID of the bundle to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(response=200, description="Bundle and its associated products deleted successfully."),
     *     @OA\Response(response=404, description="Bundle not found."),
     *     @OA\Response(response=500, description="Unexpected server error.")
     * )
     */
    public function destroy(string $bundleId)
    {
        try {
            $this->bundleProductService->deleteBundle((int) $bundleId);

            return ApiResponse::success('Bundle and its associated products deleted successfully.', 200);
        } catch (NotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        } catch (\Exception $e) {
            return ApiResponse::error('Unexpected error: ' . $e->getMessage(), 500);
        }
    }
}
