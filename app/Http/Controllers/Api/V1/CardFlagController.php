<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CardFlagDeleteImageRequest;
use App\Http\Requests\Api\V1\CardFlagDestroyRequest;
use App\Http\Requests\Api\V1\CardFlagFilterRequest;
use App\Http\Requests\Api\V1\CardFlagStoreRequest;
use App\Http\Requests\Api\V1\CardFlagUpdateImageRequest;
use App\Http\Requests\Api\V1\CardFlagUpdateRequest;
use App\Services\Api\V1\CardFlagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CardFlagController extends Controller
{
    //
    public function store(CardFlagStoreRequest $request): JsonResponse
    {
        $resp = app(CardFlagService::class)->store($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function index(CardFlagFilterRequest $request): JsonResponse
    {
        $resp = app(CardFlagService::class)->index($request);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function update(CardFlagUpdateRequest $request, int $id): JsonResponse
    {
        $resp = app(CardFlagService::class)->update($request->validated(), $id);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function updateImage(CardFlagUpdateImageRequest $request, int $id): JsonResponse
    {
        $resp = app(CardFlagService::class)->updateImage($request, $id);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function destroy(CardFlagDestroyRequest $request, int $cardFlag): JsonResponse
    {
        $resp = app(CardFlagService::class)->destroy($cardFlag);
        return $resp->response()->setStatusCode($resp['status_code']);
    }

    public function deleteImage(CardFlagDeleteImageRequest $request, int $id): JsonResponse
    {
        $resp = app(CardFlagService::class)->deleteImage($id);
        return $resp->response()->setStatusCode($resp['status_code']);
    }
}
