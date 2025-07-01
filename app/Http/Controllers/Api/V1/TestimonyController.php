<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TestimonyApproveRequest;
use App\Http\Requests\Api\V1\TestimonyDeletePhotoRequest;
use App\Http\Requests\Api\V1\TestimonyDestroyRequest;
use App\Http\Requests\Api\V1\TestimonyFilterRequest;
use App\Http\Requests\Api\V1\TestimonyRejectRequest;
use App\Http\Requests\Api\V1\TestimonyStoreRequest;
use App\Http\Requests\Api\V1\TestimonyUpdatePhotoRequest;
use App\Http\Requests\Api\V1\TestimonyUpdateRequest;
use App\Services\Api\V1\TestimonyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestimonyController extends Controller
{
    //

    public function index(TestimonyFilterRequest $request): JsonResponse
    {
        $resp = app(TestimonyService::class)->index($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function store(TestimonyStoreRequest $request): JsonResponse
    {
        $resp = app(TestimonyService::class)->store($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function update(int $id, TestimonyUpdateRequest $request): JsonResponse
    {
        $resp = app(TestimonyService::class)->update($id, $request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function updatePhoto(int $id, TestimonyUpdatePhotoRequest $request): JsonResponse
    {
        $resp = app(TestimonyService::class)->updatePhoto($id, $request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function deletePhoto(int $id, TestimonyDeletePhotoRequest $request): JsonResponse
    {
        $resp = app(TestimonyService::class)->deletePhoto($id, $request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function destroy(int $id, TestimonyDestroyRequest $request): JsonResponse
    {
        $resp = app(TestimonyService::class)->destroy($id, $request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function approve(int $id, TestimonyApproveRequest $request): JsonResponse
    {
        $resp = app(TestimonyService::class)->approve($id, $request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function reject(int $id, TestimonyRejectRequest $request): JsonResponse
    {
        $resp = app(TestimonyService::class)->reject($id, $request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }
}
