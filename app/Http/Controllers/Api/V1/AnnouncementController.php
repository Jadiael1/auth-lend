<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Api\V1\AnnouncementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    //
    public function store(Request $request): JsonResponse
    {
        $resp = app(AnnouncementService::class)->store($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function index(Request $request): JsonResponse
    {
        $resp = app(AnnouncementService::class)->index($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $resp = app(AnnouncementService::class)->update($id, $request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function updatePhoto(int $id, Request $request): JsonResponse
    {
        $resp = app(AnnouncementService::class)->updatePhoto($id, $request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function deletePhoto(int $id): JsonResponse
    {
        $resp = app(AnnouncementService::class)->deletePhoto($id);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function remove(int $id): JsonResponse
    {
        $resp = app(AnnouncementService::class)->remove($id);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function activeAd(int $id): JsonResponse
    {
        $resp = app(AnnouncementService::class)->activeAd($id);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function disableAd(int $id): JsonResponse
    {
        $resp = app(AnnouncementService::class)->disableAd($id);
        return $resp->response()->setStatusCode($resp['status_code']);
    }
}
