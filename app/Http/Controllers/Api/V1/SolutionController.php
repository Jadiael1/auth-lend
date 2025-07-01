<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SolutionFilterRequest;
use App\Http\Requests\Api\V1\SolutionUpdateRequest;
use App\Services\Api\V1\SolutionService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Http\Request;

class SolutionController extends Controller
{
    //
    public function store(Request $request): JsonResponse
    {
        $resp = app(SolutionService::class)->store($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function index(SolutionFilterRequest $request): JsonResponse
    {
        $resp = app(SolutionService::class)->index($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function update(SolutionUpdateRequest $request, int $id): JsonResponse
    {
        $resp = app(SolutionService::class)->update($request, $id);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function updatePhoto(Request $request, int $id): JsonResponse
    {
        $resp = app(SolutionService::class)->updatePhoto($request, $id);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function deletePhoto(int $id): JsonResponse
    {
        $resp = app(SolutionService::class)->deletePhoto($id);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function destroy(int $id): JsonResponse
    {
        $resp = app(SolutionService::class)->destroy($id);
        return $resp->response()->setStatusCode($resp['status_code']);
    }
}
