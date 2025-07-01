<?php

namespace App\Services\Api\V1;

use App\Http\Requests\Api\V1\CardFlagFilterRequest;
use App\Http\Requests\Api\V1\CardFlagInstallmentLimitFilterRequest;
use App\Http\Requests\Api\V1\CardFlagStoreRequest;
use App\Http\Requests\Api\V1\CardFlagUpdateImageRequest;
use App\Http\Requests\Api\V1\SolutionFilterRequest;
use App\Http\Requests\Api\V1\SolutionUpdateRequest;
use App\Http\Requests\Api\V1\StoreFilterRequest;
use App\Http\Requests\Api\V1\StoreStoreRequest;
use App\Http\Resources\Api\V1\ResponseResource;
use App\Models\Api\V1\CardFlag;
use App\Models\Api\V1\CardFlagInstallmentLimit;
use App\Models\Api\V1\Solution;
use App\Models\Api\V1\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CardFlagInstallmentLimitService
{
    /**
     * @OA\Post(
     *     path="/api/v1/card-flag-installment-limits",
     *     summary="Create a card flag installment limit - ADM",
     *     description="Adds a new limit configuration for a card flag.",
     *     operationId="storeCardFlagInstallmentLimit",
     *     tags={"Card Flag Installment Limits"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"card_flag_id","installments","min_value"},
     *             @OA\Property(property="card_flag_id", type="integer", example=1),
     *             @OA\Property(property="installments", type="integer", example=12),
     *             @OA\Property(property="min_value", type="number", format="decimal", example=100.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Installment limit added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=201),
     *             @OA\Property(property="message", type="string", example="Installment limit created successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/CardFlagInstallmentLimit"),
     *             @OA\Property(property="errors", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Failed to create installment limit."),
     *             @OA\Property(property="errors", type="string", example="SQLSTATE...")
     *         )
     *     )
     * )
     */
    public function store(array $data): ResponseResource
    {
        try {
            DB::beginTransaction();

            $limit = CardFlagInstallmentLimit::create($data);

            DB::commit();

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_CREATED,
                'message' => 'Installment limit created successfully.',
                'data' => $limit,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to create installment limit', ['exception' => $e]);

            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to create installment limit.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/card-flag-installment-limits",
     *     summary="List installment limits",
     *     description="Returns paginated list of installment limits with optional filters.",
     *     operationId="listCardFlagInstallmentLimits",
     *     tags={"Card Flag Installment Limits"},
     *     @OA\Parameter(name="card_flag_id", in="query", @OA\Schema(type="integer"), description="Filter by card flag ID"),
     *     @OA\Parameter(name="installments", in="query", @OA\Schema(type="integer"), description="Filter by number of installments"),
     *     @OA\Parameter(name="min_value", in="query", @OA\Schema(type="number", format="decimal"), description="Filter by minimum value threshold"),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=15), description="Items per page"),
     *     @OA\Response(
     *         response=200,
     *         description="Installment limits fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example=""),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CardFlagInstallmentLimit")),
     *                 @OA\Property(property="total", type="integer", example=25)
     *             ),
     *             @OA\Property(property="errors", type="string", nullable=true)
     *         )
     *     )
     * )
     */
    public function index(CardFlagInstallmentLimitFilterRequest $request): ResponseResource
    {
        $filters = $request->validated();

        $query = CardFlagInstallmentLimit::query();

        if (!empty($filters['card_flag_id'])) {
            $query->where('card_flag_id', $filters['card_flag_id']);
        }
        if (!empty($filters['installments'])) {
            $query->where('installments', $filters['installments']);
        }
        if (!empty($filters['min_value'])) {
            $query->where('min_value', $filters['min_value']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => '',
            'data' => $query->paginate($perPage),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/card-flag-installment-limits/{id}",
     *     summary="Update an installment limit - ADM",
     *     description="Updates the number of installments or minimum value for an existing limit rule.",
     *     operationId="updateCardFlagInstallmentLimit",
     *     tags={"Card Flag Installment Limits"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="Installment limit ID", @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"installments","min_value"},
     *             @OA\Property(property="installments", type="integer", example=12),
     *             @OA\Property(property="min_value", type="number", format="decimal", example=100.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Installment limit updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Installment limit updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/CardFlagInstallmentLimit"),
     *             @OA\Property(property="errors", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Installment limit not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Installment limit not found."),
     *             @OA\Property(property="errors", type="string", example="No query results for model ...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(array $data, int $id): ResponseResource
    {
        try {
            $limit = CardFlagInstallmentLimit::findOrFail($id);
            $limit->update($data);

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'Installment limit updated successfully.',
                'data' => $limit,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to update installment limit', ['id' => $id, 'exception' => $e]);
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to update installment limit.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/card-flag-installment-limits/{id}",
     *     summary="Delete an installment limit - ADM",
     *     description="Removes a specific installment limit configuration.",
     *     operationId="destroyCardFlagInstallmentLimit",
     *     tags={"Card Flag Installment Limits"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         description="Installment limit ID", @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Installment limit deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Installment limit deleted successfully."),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Installment limit not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Installment limit not found."),
     *             @OA\Property(property="errors", type="string", example="No query results for model ...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Failed to delete installment limit."),
     *             @OA\Property(property="errors", type="string", example="SQLSTATE...")
     *         )
     *     )
     * )
     */
    public function destroy(int $id): ResponseResource
    {
        try {
            $limit = CardFlagInstallmentLimit::findOrFail($id);
            $limit->delete();

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'Installment limit deleted successfully.',
                'data' => null,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to delete installment limit', ['id' => $id, 'exception' => $e]);

            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to delete installment limit.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }
}
