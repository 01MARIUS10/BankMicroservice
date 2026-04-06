<?php

namespace App\Presentation\Http\Responses;
use Log;
use Illuminate\Http\JsonResponse;

class ApiResponse
{

    public function created($data, string $message = 'Resource created successfully'): JsonResponse {
        return response()->json([
            'data'    => $data,
            'status'  => 'success',
            'message' => $message,
        ], 201);
    }

    public function success($data,string $message = 'Operation completed successfully'): JsonResponse{
        return response()->json([
            'data' => $data,
            'status' => 'success',
            'message' => $message,
        ], 200);
    }
    public function exceptionError(\Exception $e): JsonResponse
    {
        Log::warning('exception error', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
        ]);
        return response()->json([
            'data' => null,
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 400);

    }

    public function systemError(\Error $e): JsonResponse
    {
        Log::critical('System error', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);
        return response()->json([
            'data' => null,
            'status' => 'error',
            'message' => 'A critical error occurred. Please try again later.',
        ], 500);

    }

    public function validationError($data,string $message = 'Validation error'): JsonResponse{
        return response()->json([
            'data' => $data,
            'status' => 'validation_error',
            'message' => $message,
        ], 400);
    }
    public function notFound(string $message = "ressource introuvable"): JsonResponse{
        return response()->json([
            'data' => null,
            'status' => 'not_found',
            'message' => $message,
        ], 404);
    }

    public function unAuthorize(): JsonResponse {
        return response()->json([
            'data'    => null,
            'status'  => 'unauthorized',
            'message' => 'Unauthorized',
        ], 401);
    }
}
