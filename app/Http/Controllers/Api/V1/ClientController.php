<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ClientStoreRequest;
use App\Http\Requests\Api\V1\ClientUpdateCpfBackPhotoRequest;
use App\Http\Requests\Api\V1\ClientUpdateCpfFrontPhotoRequest;
use App\Http\Requests\Api\V1\ClientUpdateRequest;
use App\Http\Requests\Api\V1\ClientUpdateRgBackPhotoRequest;
use App\Http\Requests\Api\V1\ClientUpdateRgFrontPhotoRequest;
use App\Services\Api\V1\ClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    //
    public function index(Request $request): JsonResponse
    {
        $resp = app(ClientService::class)->index($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function store(ClientStoreRequest $request): JsonResponse
    {
        $resp = app(ClientService::class)->store($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function update(int $id, ClientUpdateRequest $request): JsonResponse
    {
        $resp = app(ClientService::class)->update($id, $request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function remove(int $id, Request $request): JsonResponse
    {
        $resp = app(ClientService::class)->remove($id);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function updateCpfFrontPhoto(int $id, ClientUpdateCpfFrontPhotoRequest $request): JsonResponse
    {
        $resp = app(ClientService::class)->updateCpfFrontPhoto($id, $request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function deleteCpfFrontPhoto(int $id, Request $request): JsonResponse
    {
        $resp = app(ClientService::class)->deleteCpfFrontPhoto($id, $request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function updateCpfBackPhoto(int $id, ClientUpdateCpfBackPhotoRequest $request): JsonResponse
    {
        $resp = app(ClientService::class)->updateCpfBackPhoto($id, $request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function deleteCpfBackPhoto(int $id, Request $request): JsonResponse
    {
        $resp = app(ClientService::class)->deleteCpfBackPhoto($id, $request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function updateRgFrontPhoto(int $id, ClientUpdateRgFrontPhotoRequest $request): JsonResponse
    {
        $resp = app(ClientService::class)->updateRgFrontPhoto($id, $request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function deleteRgFrontPhoto(int $id, Request $request): JsonResponse
    {
        $resp = app(ClientService::class)->deleteRgFrontPhoto($id, $request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function updateRgBackPhoto(int $id, ClientUpdateRgBackPhotoRequest $request): JsonResponse
    {
        $resp = app(ClientService::class)->updateRgBackPhoto($id, $request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function deleteRgBackPhoto(int $id, Request $request): JsonResponse
    {
        $resp = app(ClientService::class)->deleteRgBackPhoto($id, $request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }
}
