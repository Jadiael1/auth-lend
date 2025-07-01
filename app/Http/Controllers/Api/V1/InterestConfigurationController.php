<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\InterestConfigurationDestroyRequest;
use App\Http\Requests\Api\V1\InterestConfigurationFilterRequest;
use App\Http\Requests\Api\V1\InterestConfigurationStoreRequest;
use App\Http\Requests\Api\V1\InterestConfigurationUpdateRequest;
use App\Services\Api\V1\InterestConfigurationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterestConfigurationController extends Controller
{
    //
    public function store(InterestConfigurationStoreRequest $request): JsonResponse
    {
        $response = app(InterestConfigurationService::class)->store($request);
        return $response->response()->setStatusCode($response['status_code']);
    }

    public function index(InterestConfigurationFilterRequest $request): JsonResponse
    {
        $resp = app(InterestConfigurationService::class)->index($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function update(InterestConfigurationUpdateRequest $request, int $id): JsonResponse
    {
        $resp = app(InterestConfigurationService::class)->update($request->validated(), $id);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function destroy(int $id, InterestConfigurationDestroyRequest $request): JsonResponse
    {
        $resp = app(InterestConfigurationService::class)->destroy($id);
        return $resp->response()->setStatusCode($resp['status_code']);
    }
}
