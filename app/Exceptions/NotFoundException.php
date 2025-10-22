<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class NotFoundException extends Exception
{
    public function render($request): JsonResponse
    {
        return response()->json([
            'error' => [
                'message' => $this->getMessage(),
                'status' => 404,
            ]
        ], 404);
    }
}
