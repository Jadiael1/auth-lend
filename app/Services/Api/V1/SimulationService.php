<?php

namespace App\Services\Api\V1;

use App\Http\Requests\Api\V1\SimulationRequest;
use App\Http\Resources\Api\V1\ResponseResource;
use App\Models\Api\V1\CardFlag;
use App\Models\Api\V1\InterestConfiguration;
use App\Models\Api\V1\Simulation;
use App\Models\Api\V1\Store;
use App\Models\Api\V1\ValueType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SimulationService
{

    /**
     * @OA\Post(
     *     path="/api/v1/simulations",
     *     summary="Perform a new simulation",
     *     tags={"Simulations"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount", "installments", "value_type_id", "store_id", "card_flag_id"},
     *             @OA\Property(property="amount", type="number", format="float", example=1000.00),
     *             @OA\Property(property="installments", type="integer", example=12),
     *             @OA\Property(property="value_type_id", type="integer", example=1),
     *             @OA\Property(property="store_id", type="integer", example=3),
     *             @OA\Property(property="card_flag_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Simulation calculated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example=""),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="amount", type="number", format="float", example=1000.00),
     *                 @OA\Property(property="amount_with_interest", type="number", format="float", example=1120.50),
     *                 @OA\Property(property="interest_rate_by_type_of_amount", type="number", format="float", example=1.5),
     *                 @OA\Property(property="interest_rate_by_number_of_installments", type="number", format="float", example=2.5),
     *                 @OA\Property(property="value_type", type="string", example="valor desejado"),
     *                 @OA\Property(property="installments", type="integer", example=12),
     *                 @OA\Property(property="installment_value", type="number", format="float", example=50.51),
     *                 @OA\Property(property="store_name", type="string", example="AuthLend Recife"),
     *                 @OA\Property(property="store_city", type="string", example="Recife"),
     *                 @OA\Property(property="card_flag", type="string", example="Visa"),
     *                 @OA\Property(property="simulation_id", type="string", format="uuid", example="14a7cf88-92b4-4f37-9f48-0bd419e8f210")
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Some referenced resource not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation or business rule error"
     *     )
     * )
     */
    public function simulate(SimulationRequest $request): ResponseResource
    {
        $data = $request->validated();
        $finalAmount = 0;
        $installmentValue = 0;

        $cardFlag = CardFlag::find($data['card_flag_id']);
        $valueType = ValueType::find($data['value_type_id']);
        $store = Store::find($data['store_id']);

        if (!$cardFlag || !$valueType || !$store) {
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_NOT_FOUND,
                'message' => 'Resource not found.',
                'data' => null,
                'errors' => null,
            ]);
        }

        $installments = $data['installments'];
        $amount = $data['amount'];
        if ($installments > $cardFlag->cardFlagInstallmentLimit->installments) {
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Number of installments exceeds the limit allowed for this flag.',
                'data' => null,
                'errors' => null,
            ]);
        }

        if ((float)$amount < (float) $cardFlag->cardFlagInstallmentLimit->min_value) {
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'The requested amount is below the minimum allowed for this installment option.',
                'data' => null,
                'errors' => [
                    'amount' => ["The minimum allowed amount is {$cardFlag->cardFlagInstallmentLimit->min_value}."]
                ],
            ]);
        }

        $config = InterestConfiguration::where([
            'card_flag_id' => $data['card_flag_id'],
            'store_id' => $data['store_id'],
            'installments' => $data['installments'],
            'value_type_id' => $data['value_type_id'],
        ])->first();

        if (!$config) {
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_NOT_FOUND,
                'message' => 'No configuration found for the selected parameters.',
                'data' => null,
                'errors' => null,
            ]);
        }



        if (strtolower($valueType->direction) === 'asc') {
            $gross = (float) $amount * (1 + ((float) $valueType->interest_rate / 100));
            $gross = $gross * (1 + ((float) $config->interest_rate / 100));
            $installmentValue = $installments > 0 ? ($installments === 1 ? floor($gross * pow(10, 2)) / pow(10, 2) : ceil(($gross / $installments) * pow(10, 2)) / pow(10, 2)) : $installments;
            $finalAmount = $gross;
            // $finalAmount = (float) $amount * (1 + ((float) $valueType->interest_rate / 100));
            // $finalAmount = (float) $finalAmount * (1 + ((float) $config->interest_rate / 100));
        }

        if (strtolower($valueType->direction) === 'desc') {
            $gross = (float) $amount * (1 - ((float) $valueType->interest_rate / 100));
            $gross = $gross * (1 - ((float) $config->interest_rate / 100));
            $installmentValue = $installments > 0 ? ($installments === 1 ? floor($amount * pow(10, 2)) / pow(10, 2) : floor(($amount / $installments) * pow(10, 2)) / pow(10, 2)) : $installments;
            $finalAmount = $gross;
            // $finalAmount = (float) $amount * (1 - ((float) $valueType->interest_rate / 100));
            // $finalAmount = (float) $finalAmount * (1 - ((float) $config->interest_rate / 100));
        }

        $factor = pow(10, 2);
        $finalAmount = floor($finalAmount * $factor) / $factor;

        $simulation = Simulation::create([
            'uuid' => (string) Str::uuid(),
            'amount' => number_format((float)$amount, 2, '.', ''),
            'amount_with_interest' => number_format((float)$finalAmount, 2, '.', ''),
            'interest_rate_by_type_of_amount' => number_format((float)$valueType->interest_rate, 2, '.', ''),
            'interest_rate_by_number_of_installments' => number_format((float)$config->interest_rate, 2, '.', ''),
            'value_type_id' => $valueType->id,
            'installments' => $installments,
            'installment_value' => $installmentValue,
            'store_id' => $store->id,
            'card_flag_id' => $cardFlag->id,
            'ip' => $request->ip(),
        ]);

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => '',
            'data' => [
                'amount' => number_format((float)$amount, 2, '.', ''),
                'amount_with_interest' => number_format((float)$finalAmount, 2, '.', ''),
                'interest_rate_by_type_of_amount' => number_format((float)$valueType->interest_rate, 2, '.', ''),
                'interest_rate_by_number_of_installments' => number_format((float)$config->interest_rate, 2, '.', ''),
                'value_type' => $valueType->type,
                'installments' => $installments,
                'installment_value' => $installmentValue,
                'store_name' => $store->name,
                'store_city' => $store->address->city,
                'card_flag' => $cardFlag->name,
                'simulation_id' => $simulation->uuid,
            ],
            'errors' => null
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/simulations/{uuid}",
     *     summary="Get simulation details by UUID",
     *     tags={"Simulations"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID of the simulation",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="d0ea79b4-c3e8-4544-bd61-8258403d995b")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Simulation details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example=""),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     allOf={
     *                         @OA\Schema(ref="#/components/schemas/Simulation"),
     *                         @OA\Schema(
     *                             @OA\Property(property="store", ref="#/components/schemas/Store"),
     *                             @OA\Property(property="card_flag", ref="#/components/schemas/CardFlag"),
     *                             @OA\Property(property="value_type", ref="#/components/schemas/ValueType"),
     *                         ),
     *                     },
     *                 ),
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Simulation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Simulation not found."),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     )
     * )
     */
    public function show(string $uuid): ResponseResource
    {
        $simulation = Simulation::with(['store.address', 'cardFlag', 'valueType'])->where('uuid', $uuid)->first();

        if (!$simulation) {
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_NOT_FOUND,
                'message' => 'Simulation not found.',
                'data' => null,
                'errors' => null,
            ]);
        }

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => '',
            'data' => $simulation,
            'errors' => null,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/simulations",
     *     summary="List simulations with filters",
     *     tags={"Simulations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="installments", in="query", required=false, description="Number of installments", @OA\Schema(type="integer", example=12)),
     *     @OA\Parameter(name="installment_value", in="query", required=false, description="Installment value", @OA\Schema(type="number", format="float", example=55.48)),
     *     @OA\Parameter(name="store_id", in="query", required=false, description="Store ID", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="card_flag_id", in="query", required=false, description="Card Flag ID", @OA\Schema(type="integer", example=2)),
     *     @OA\Parameter(name="value_type_id", in="query", required=false, description="Value Type ID", @OA\Schema(type="integer", example=3)),
     *     @OA\Parameter(name="min_amount", in="query", required=false, description="Minimum amount", @OA\Schema(type="number", format="float", example=100)),
     *     @OA\Parameter(name="max_amount", in="query", required=false, description="Maximum amount", @OA\Schema(type="number", format="float", example=5000)),
     *     @OA\Parameter(name="created_from", in="query", required=false, description="Start date (YYYY-MM-DD)", @OA\Schema(type="string", format="date", example="2025-06-01")),
     *     @OA\Parameter(name="created_to", in="query", required=false, description="End date (YYYY-MM-DD)", @OA\Schema(type="string", format="date", example="2025-06-10")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page", @OA\Schema(type="integer", example=10)),
     *     @OA\Parameter(name="page", in="query", required=false, description="Current page", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(
     *         response=200,
     *         description="List of simulations",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example=""),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         allOf={
     *                             @OA\Schema(ref="#/components/schemas/Simulation"),
     *                             @OA\Schema(
     *                                 @OA\Property(property="store", ref="#/components/schemas/Store"),
     *                                 @OA\Property(property="card_flag", ref="#/components/schemas/CardFlag"),
     *                                 @OA\Property(property="value_type", ref="#/components/schemas/ValueType"),
     *                             ),
     *                         },
     *                     ),
     *                 ),
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function index(Request $request): ResponseResource
    {
        $validated = validator($request->all(), [
            'installments'   => ['nullable', 'integer', 'min:1'],
            'store_id'       => ['nullable', 'integer', 'exists:stores,id'],
            'card_flag_id'   => ['nullable', 'integer', 'exists:card_flags,id'],
            'value_type_id'  => ['nullable', 'integer', 'exists:value_types,id'],
            'min_amount'     => ['nullable', 'numeric', 'min:0'],
            'max_amount'     => ['nullable', 'numeric', 'min:0'],
            'created_from'   => ['nullable', 'date'],
            'created_to'     => ['nullable', 'date', 'after_or_equal:created_from'],
            'per_page'       => ['nullable', 'integer', 'min:1', 'max:100'],
            'page'           => ['nullable', 'integer', 'min:1'],
        ])->validate();

        $query = Simulation::query()->with(['store.address', 'cardFlag', 'valueType']);

        if (isset($validated['installments'])) {
            $query->where('installments', $validated['installments']);
        }

        if (isset($validated['installment_value'])) {
            $query->where('installment_value', $validated['installment_value']);
        }

        if (isset($validated['store_id'])) {
            $query->where('store_id', $validated['store_id']);
        }

        if (isset($validated['card_flag_id'])) {
            $query->where('card_flag_id', $validated['card_flag_id']);
        }

        if (isset($validated['value_type_id'])) {
            $query->where('value_type_id', $validated['value_type_id']);
        }

        if (isset($validated['min_amount'])) {
            $query->where('amount', '>=', $validated['min_amount']);
        }

        if (isset($validated['max_amount'])) {
            $query->where('amount', '<=', $validated['max_amount']);
        }

        if (isset($validated['created_from'])) {
            $query->whereDate('created_at', '>=', $validated['created_from']);
        }

        if (isset($validated['created_to'])) {
            $query->whereDate('created_at', '<=', $validated['created_to']);
        }

        $perPage = $validated['per_page'] ?? 10;

        $paginated = $query->orderByDesc('id')->paginate($perPage);

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => '',
            'data' => $paginated,
            'errors' => null,
        ]);
    }


    /**
     * @OA\Patch(
     *     path="/api/v1/simulations/{id}",
     *     summary="Update a simulation record",
     *     tags={"Simulations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Simulation ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="amount", type="number", format="float", example=1100.00),
     *             @OA\Property(property="amount_with_interest", type="number", format="float", example=1150.00),
     *             @OA\Property(property="interest_rate_by_type_of_amount", type="number", format="float", example=1.5),
     *             @OA\Property(property="interest_rate_by_number_of_installments", type="number", format="float", example=0.5),
     *             @OA\Property(property="installments", type="integer", example=12),
     *             @OA\Property(property="installment_value", type="number", format="float", example=55.48),
     *             @OA\Property(property="ip", type="string", format="ipv4", example="192.168.1.20")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Simulation updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Simulation updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Simulation"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Simulation not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, int $id): ResponseResource
    {
        $simulation = Simulation::find($id);

        if (!$simulation) {
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_NOT_FOUND,
                'message' => 'Simulation not found.',
                'data' => null,
                'errors' => null,
            ]);
        }

        $validated = validator($request->all(), [
            'amount' => ['nullable', 'numeric', 'min:0'],
            'amount_with_interest' => ['nullable', 'numeric', 'min:0'],
            'interest_rate_by_type_of_amount' => ['nullable', 'numeric', 'min:0'],
            'interest_rate_by_number_of_installments' => ['nullable', 'numeric', 'min:0'],
            'installments' => ['nullable', 'integer', 'min:1'],
            'installment_value' => ['nullable', 'numeric', 'min:0'],
            'ip' => ['nullable', 'ip'],
        ])->validate();

        $simulation->update($validated);

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'Simulation updated successfully.',
            'data' => $simulation,
            'errors' => null,
        ]);
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/simulations/{id}",
     *     summary="Delete a simulation record",
     *     description="Removes a simulation from the system by its ID.",
     *     tags={"Simulations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Simulation ID",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Simulation deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Simulation deleted successfully."),
     *             @OA\Property(property="data", type="object", example={"id": 1}),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Simulation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Simulation not found."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function destroy(Request $request, int $id): ResponseResource
    {
        $simulation = Simulation::find($id);

        if (!$simulation) {
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_NOT_FOUND,
                'message' => 'Simulation not found.',
                'data' => null,
                'errors' => null,
            ]);
        }

        $simulation->delete();

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'Simulation deleted successfully.',
            'data' => ['id' => $id],
            'errors' => null,
        ]);
    }
}
