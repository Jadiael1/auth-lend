<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Api\V1\User;
use App\Services\Api\V1\UserService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $resp = app(UserService::class)->index($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function show(Request $request): JsonResponse
    {
        $resp = app(UserService::class)->show($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function store(array $request): JsonResponse
    {
        $resp = app(UserService::class)->store($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function updatePermissions(Request $request, User $user): JsonResponse
    {
        $resp = app(UserService::class)->updatePermissions($request, $user);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function updateIsAdmin(Request $request, User $user): JsonResponse
    {
        $resp = app(UserService::class)->updateIsAdmin($request, $user);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $resp = app(UserService::class)->updateStatus($request, $user);
        return $resp->response()->setStatusCode($resp['status_code']);
    }
}
