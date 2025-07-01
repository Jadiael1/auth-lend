<?php

namespace App\Exceptions\Api\V1;

use Exception;
use App\Http\Resources\Api\V1\ResponseResource;

class ApiFormattedException extends Exception
{
    //
    public static function configure(\Illuminate\Foundation\Configuration\Exceptions $exceptions): void
    {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, \Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse {
            return (new ResponseResource([
                'status' => 'error',
                'message' => 'Unauthenticated.',
                'data' => null,
                'errors' => null,
            ]))->response()->setStatusCode(401);
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, \Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse {
            return (new ResponseResource([
                'status' => 'error',
                'message' => 'Validation error.',
                'data' => null,
                'errors' => $e->errors(),
            ]))->response()->setStatusCode(422);
        });
    }
}
