<?php

namespace App\Services\Api\V1;

use App\Http\Resources\Api\V1\ResponseResource;
use App\Models\Api\V1\ValueType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValueTypeService
{
    /**
     * @OA\Post(
     *     path="/api/v1/value-types",
     *     summary="Create a new Value Type",
     *     tags={"Value Types"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type", "interest_rate"},
     *             @OA\Property(property="type", type="string", example="Valor Desejado"),
     *             @OA\Property(property="interest_rate", type="number", format="float", example=2.75)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Value Type created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ValueType")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function store(Request $request): ResponseResource
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'max:255', 'unique:value_types,type'],
            'interest_rate' => ['required', 'numeric', 'min:0'],
            'direction' =>  ['required', 'in:ASC,DESC']
        ]);

        try {
            $valueType = ValueType::create($validated);

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_CREATED,
                'message' => 'Value Type created successfully.',
                'data' => $valueType,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to create value type', ['exception' => $e]);
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to create value type.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/value-types",
     *     summary="List Value Types (paginated)",
     *     tags={"Value Types"},
     *     @OA\Parameter(name="type", in="query", required=false, description="Filter by type", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page", @OA\Schema(type="integer", default=15)),
     *     @OA\Response(
     *         response=200,
     *         description="Value Types retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example=""),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="errors", type="string", nullable=true)
     *         )
     *     )
     * )
     */
    public function index(Request $request): ResponseResource
    {
        $query = ValueType::query();

        if ($request->filled('type')) {
            $query->where('type', 'like', '%' . $request->get('type') . '%');
        }

        $perPage = $request->get('per_page', 15);

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
     *     path="/api/v1/value-types/{id}",
     *     summary="Update a Value Type",
     *     tags={"Value Types"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type", "interest_rate"},
     *             @OA\Property(property="type", type="string", example="Limite Total"),
     *             @OA\Property(property="interest_rate", type="number", example=1.5)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Value Type updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Value Type not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function update(int $id, Request $request): ResponseResource
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'max:255', "unique:value_types,type,{$id}"],
            'interest_rate' => ['required', 'numeric', 'min:0'],
            'direction' =>  ['required', 'in:asc,desc,ASC,DESC']
        ]);

        try {
            $valueType = ValueType::findOrFail($id);
            $valueType->update($validated);

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'Value Type updated successfully.',
                'data' => $valueType,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to update value type', ['id' => $id, 'exception' => $e]);
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to update value type.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/value-types/{id}",
     *     summary="Delete a Value Type",
     *     tags={"Value Types"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Value Type ID", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Value Type deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Value Type not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function destroy(int $id): ResponseResource
    {
        try {
            $valueType = ValueType::findOrFail($id);
            $valueType->delete();

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'Value Type deleted successfully.',
                'data' => null,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to delete value type', ['id' => $id, 'exception' => $e]);
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to delete value type.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }
}
