<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SignUpRequest;
use App\Services\Api\V1\AuthService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AuthController extends Controller
{
    public function user(Request $request): JsonResponse
    {
        $controller = app(UserController::class);
        return $controller->show($request);
    }

    public function signup(SignUpRequest $request): JsonResponse
    {
        $controller = app(UserController::class);
        return $controller->store($request->validated());
    }

    public function signin(Request $request): JsonResponse
    {
        $authService = app(AuthService::class);
        $resp = $authService->signin($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function signout(Request $request)
    {
        $authService = app(AuthService::class);
        $resp = $authService->signout($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }
}
