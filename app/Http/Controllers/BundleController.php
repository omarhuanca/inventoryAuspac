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

    public function index()
    {
        try {
            $bundles = $this->bundleService->getAllBundles();
            return ApiResponse::success('Bundle list.', 200, BundleResource::collection($bundles));
        } catch (\Exception $e) {
            return ApiResponse::error('Error obtaining the list of bundles: ' . $e->getMessage(), 500);
        }
    }

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
