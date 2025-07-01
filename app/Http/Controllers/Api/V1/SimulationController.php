<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SimulationRequest;
use App\Services\Api\V1\SimulationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SimulationController extends Controller
{
    //
    public function simulate(SimulationRequest $request): JsonResponse
    {
        $resp = app(SimulationService::class)->simulate($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function show(string $uuid): JsonResponse
    {
        $resp = app(SimulationService::class)->show($uuid);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function index(Request $request): JsonResponse
    {
        $resp = app(SimulationService::class)->index($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $resp = app(SimulationService::class)->update($request, $id);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $resp = app(SimulationService::class)->destroy($request, $id);
        return $resp->response()->setStatusCode($resp['status_code']);
    }
}
