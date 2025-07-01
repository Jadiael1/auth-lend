<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CardFlagInstallmentLimitDestroyRequest;
use App\Http\Requests\Api\V1\CardFlagInstallmentLimitFilterRequest;
use App\Http\Requests\Api\V1\CardFlagInstallmentLimitStoreRequest;
use App\Http\Requests\Api\V1\CardFlagInstallmentLimitUpdateRequest;
use App\Services\Api\V1\CardFlagInstallmentLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CardFlagInstallmentLimitController extends Controller
{
    //
    public function store(CardFlagInstallmentLimitStoreRequest $request): JsonResponse
    {
        $resp = app(CardFlagInstallmentLimitService::class)->store($request->validated());
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function index(CardFlagInstallmentLimitFilterRequest $request): JsonResponse
    {
        $resp = app(CardFlagInstallmentLimitService::class)->index($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function update(CardFlagInstallmentLimitUpdateRequest $request, int $id): JsonResponse
    {
        $resp = app(CardFlagInstallmentLimitService::class)->update($request->validated(), $id);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function destroy(CardFlagInstallmentLimitDestroyRequest $request, int $id): JsonResponse
    {
        $resp = app(CardFlagInstallmentLimitService::class)->destroy($id);
        return $resp->response()->setStatusCode($resp['status_code']);
    }
}
