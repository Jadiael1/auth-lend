<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreFilterRequest;
use App\Http\Requests\Api\V1\StoreStoreRequest;
use App\Services\Api\V1\StoreService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    //
    public function store(StoreStoreRequest $request): JsonResponse
    {
        $resp = app(StoreService::class)->store($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function index(StoreFilterRequest $request): JsonResponse
    {
        $resp = app(StoreService::class)->index($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function updatePhoto(int $storeId, int $photoId, Request $request): JsonResponse
    {
        $resp = app(StoreService::class)->updatePhoto($storeId, $photoId, $request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function deletePhoto(int $storeId, int $photoId): JsonResponse
    {
        $resp = app(StoreService::class)->deletePhoto($storeId, $photoId);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function addPhoto(int $storeId, Request $request): JsonResponse
    {
        $resp = app(StoreService::class)->addPhoto($storeId, $request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function update(int $storeId, Request $request): JsonResponse
    {
        $resp = app(StoreService::class)->update($storeId, $request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function destroy(int $storeId): JsonResponse
    {
        $resp = app(StoreService::class)->destroy($storeId);
        return $resp->response()->setStatusCode($resp['status_code']);
    }
}
