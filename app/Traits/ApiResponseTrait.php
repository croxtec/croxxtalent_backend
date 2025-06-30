<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Lang;

trait ApiResponseTrait
{
    /**
     * Success response with localized message
     */
    protected function successResponse($data = null, string $messageKey = 'api.success.retrieved', array $messageParams = [], int $statusCode = Response::HTTP_OK): JsonResponse
    {
        $response = [
            'status' => true,
            'message' => Lang::get($messageKey, $messageParams),
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Error response with localized message
     */
    protected function errorResponse(string $messageKey = 'api.errors.server_error', array $messageParams = [], int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR, $errors = null): JsonResponse
    {
        $response = [
            'status' => false,
            'message' => Lang::get($messageKey, $messageParams),
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Validation error response
     */
    protected function validationErrorResponse($errors, string $messageKey = 'api.errors.validation_failed'): JsonResponse
    {
        return $this->errorResponse(
            $messageKey,
            [],
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $errors
        );
    }

    /**
     * Not found response
     */
    protected function notFoundResponse(string $messageKey = 'api.errors.not_found', array $messageParams = []): JsonResponse
    {
        return $this->errorResponse($messageKey, $messageParams, Response::HTTP_NOT_FOUND);
    }

    /**
     * Unauthorized response
     */
    protected function unauthorizedResponse(string $messageKey = 'api.errors.unauthorized', array $messageParams = []): JsonResponse
    {
        return $this->errorResponse($messageKey, $messageParams, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Forbidden response
     */
    protected function forbiddenResponse(string $messageKey = 'api.errors.forbidden', array $messageParams = []): JsonResponse
    {
        return $this->errorResponse($messageKey, $messageParams, Response::HTTP_FORBIDDEN);
    }

    /**
     * Paginated response
     */
    protected function paginatedResponse($paginator, string $messageKey = 'api.success.retrieved'): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => Lang::get($messageKey),
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'meta' => [
                'info' => Lang::get('api.pagination.showing', [
                    'from' => $paginator->firstItem() ?? 0,
                    'to' => $paginator->lastItem() ?? 0,
                    'total' => $paginator->total()
                ])
            ]
        ]);
    }
}