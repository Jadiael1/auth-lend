<?php

namespace App\Services\Api\V1;

use App\Http\Requests\Api\V1\InterestConfigurationFilterRequest;
use App\Http\Requests\Api\V1\InterestConfigurationStoreRequest;
use App\Http\Resources\Api\V1\ResponseResource;
use App\Models\Api\V1\CardFlagInstallmentLimit;
use App\Models\Api\V1\InterestConfiguration;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class InterestConfigurationService
{

    /**
     * @OA\Post(
     *     path="/api/v1/interest-configurations",
     *     summary="Create a new interest configuration",
     *     description="Stores a new interest rate configuration for a specific card flag, store and number of installments.",
     *     operationId="storeInterestConfiguration",
     *     tags={"Interest Configurations"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"card_flag_id", "store_id", "installments", "value_type_id", "interest_rate"},
     *             @OA\Property(property="card_flag_id", type="integer", example=1),
     *             @OA\Property(property="store_id", type="integer", example=1),
     *             @OA\Property(property="installments", type="integer", example=6),
     *             @OA\Property(property="value_type_id", type="integer", example=1),
     *             @OA\Property(property="interest_rate", type="number", format="float", example=2.5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Interest configuration created successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=201),
     *             @OA\Property(property="message", type="string", example="Interest configuration created successfully."),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/InterestConfiguration"),
     *             @OA\Property(property="errors", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Installment not allowed for selected card flag.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="Installments not allowed for this card flag."),
     *             @OA\Property(property="errors", type="string", example="Installments exceed the configured limit.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Failed to create interest configuration."),
     *             @OA\Property(property="errors", type="string", example="SQLSTATE...")
     *         )
     *     )
     * )
     */
    public function store(InterestConfigurationStoreRequest $request): ResponseResource
    {
        $validated = $request->validated();

        // 🔒 Validate installment limit
        $limit = CardFlagInstallmentLimit::where('card_flag_id', $validated['card_flag_id'])
            ->where('installments', '>=', $validated['installments'])
            ->exists();

        if (!$limit) {
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_BAD_REQUEST,
                'message' => 'Installments not allowed for this card flag.',
                'data' => null,
                'errors' => 'Installments exceed the configured limit.'
            ]);
        }

        try {
            $config = InterestConfiguration::create($validated);

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_CREATED,
                'message' => 'Interest configuration created successfully.',
                'data' => $config,
                'errors' => null
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to create interest configuration', [
                'exception' => $e
            ]);
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to create interest configuration.',
                'data' => null,
                'errors' => $e->getMessage()
            ]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/interest-configurations",
     *     summary="List interest configurations",
     *     description="Retrieves a paginated list of interest configurations with optional filters.",
     *     operationId="getInterestConfigurations",
     *     tags={"Interest Configurations"},
     *     @OA\Parameter(name="card_flag_id", in="query", required=false, @OA\Schema(type="integer"), description="Card flag ID"),
     *     @OA\Parameter(name="store_id", in="query", required=false, @OA\Schema(type="integer"), description="Store ID"),
     *     @OA\Parameter(name="installments", in="query", required=false, @OA\Schema(type="integer"), description="Installments"),
     *     @OA\Parameter(name="value_type_id", in="query", required=false, @OA\Schema(type="integer"), description="Value Type ID"),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer"), description="Items per page (default 15)"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example=""),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="data", ref="#/components/schemas/InterestConfiguration"),
     *             ),
     *             @OA\Property(property="errors", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(InterestConfigurationFilterRequest $request): ResponseResource
    {
        $filters = $request->validated();

        $query = InterestConfiguration::query();

        if (!empty($filters['card_flag_id'])) {
            $query->where('card_flag_id', $filters['card_flag_id']);
        }

        if (!empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        if (!empty($filters['value_type_id'])) {
            $query->where('value_type_id', $filters['value_type_id']);
        }

        if (!empty($filters['installments'])) {
            $query->where('installments', $filters['installments']);
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
     *     path="/api/v1/interest-configurations/{id}",
     *     summary="Update an interest configuration - ADM",
     *     description="Updates an existing interest configuration based on given parameters.",
     *     operationId="updateInterestConfiguration",
     *     tags={"Interest Configurations"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="InterestConfiguration ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"card_flag_id", "store_id", "installments", "value_type_id", "interest_rate"},
     *             @OA\Property(property="card_flag_id", type="integer", example=1),
     *             @OA\Property(property="store_id", type="integer", example=1),
     *             @OA\Property(property="installments", type="integer", example=12),
     *             @OA\Property(property="value_type_id", type="integer", example=1),
     *             @OA\Property(property="interest_rate", type="number", format="float", example=3.75)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Interest configuration updated successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Interest configuration updated successfully."),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="errors", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation failed"),
     *     @OA\Response(response=404, description="Interest configuration not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function update(array $data, int $id): ResponseResource
    {
        try {
            $limit = CardFlagInstallmentLimit::where('card_flag_id', $data['card_flag_id'])
                ->where('installments', $data['installments'])
                ->first();

            if (!$limit) {
                return new ResponseResource([
                    'status' => 'error',
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => 'Invalid number of installments for the selected card flag.',
                    'data' => null,
                    'errors' => ['installments' => ['This number of installments is not allowed for this card flag.']],
                ]);
            }

            $interest = InterestConfiguration::findOrFail($id);
            $interest->update($data);

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'Interest configuration updated successfully.',
                'data' => $interest,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to update interest configuration: ' . $e->getMessage());
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to update interest configuration.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/interest-configurations/{id}",
     *     summary="Delete interest configuration - ADM",
     *     description="Deletes an existing interest configuration by ID.",
     *     operationId="destroyInterestConfiguration",
     *     tags={"Interest Configurations"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Interest Configuration ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Interest configuration deleted successfully."),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Interest configuration not found."),
     *             @OA\Property(property="errors", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Failed to delete interest configuration."),
     *             @OA\Property(property="errors", type="string", example="SQLSTATE...")
     *         )
     *     )
     * )
     */
    public function destroy(int $id): ResponseResource
    {
        try {
            $configuration = InterestConfiguration::findOrFail($id);
            $configuration->delete();

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'Interest configuration deleted successfully.',
                'data' => null,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to delete interest configuration', ['id' => $id, 'exception' => $e]);
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to delete interest configuration.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }
}
