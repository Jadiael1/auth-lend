<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Api\V1\ValueTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValueTypeController extends Controller
{
    //
    public function index(Request $request): JsonResponse
    {
        $resp = app(ValueTypeService::class)->index($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function store(Request $request): JsonResponse
    {
        $resp = app(ValueTypeService::class)->store($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $resp = app(ValueTypeService::class)->update($id, $request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $resp = app(ValueTypeService::class)->destroy($id);
        return $resp->response()->setStatusCode($resp['status_code']);
    }
}
