<?php

namespace App\Services\Api\V1;

use App\Http\Requests\Api\V1\CardFlagFilterRequest;
use App\Http\Requests\Api\V1\CardFlagStoreRequest;
use App\Http\Requests\Api\V1\CardFlagUpdateImageRequest;
use App\Http\Requests\Api\V1\SolutionFilterRequest;
use App\Http\Requests\Api\V1\SolutionUpdateRequest;
use App\Http\Requests\Api\V1\StoreFilterRequest;
use App\Http\Requests\Api\V1\StoreStoreRequest;
use App\Http\Resources\Api\V1\ResponseResource;
use App\Models\Api\V1\CardFlag;
use App\Models\Api\V1\Solution;
use App\Models\Api\V1\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CardFlagService
{
    /**
     * @OA\Post(
     *     path="/api/v1/card-flags",
     *     summary="Create a new card flag - ADM",
     *     description="Creates a new card flag with name and optional SVG/PNG image.",
     *     operationId="storeCardFlag",
     *     tags={"Card Flags"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name"},
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     example="Mastercard",
     *                     description="Card flag name"
     *                 ),
     *                 @OA\Property(
     *                     property="photo",
     *                     type="string",
     *                     format="binary",
     *                     nullable=true,
     *                     description="SVG or PNG image (max 2MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Card flag created successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=201),
     *             @OA\Property(property="message", type="string", example="Card flag created successfully."),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/CardFlag"),
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
     *             @OA\Property(property="message", type="string", example="Failed to create card flag."),
     *             @OA\Property(property="errors", type="string", example="SQLSTATE...")
     *         )
     *     )
     * )
     */
    public function store(CardFlagStoreRequest $request): ResponseResource
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $path = null;
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('card_flags', 'public');
            }

            $flag = CardFlag::create([
                'name' => $data['name'],
                'photo_path' => $path,
            ]);

            DB::commit();

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_CREATED,
                'message' => 'Card flag created successfully.',
                'data' => $flag,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Failed to create card flag', [
                'exception' => $e
            ]);

            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to create card flag.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/card-flags",
     *     summary="List card flags",
     *     description="Retrieves a paginated list of card flags with optional filters.",
     *     operationId="listCardFlags",
     *     tags={"Card Flags"},
     *     @OA\Parameter(name="id", in="query", required=false, description="Filter by ID", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="name", in="query", required=false, description="Filter by flag name", @OA\Schema(type="string")),
     *     @OA\Parameter(name="created_at", in="query", required=false, description="Filter by creation date", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="updated_at", in="query", required=false, description="Filter by update date", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page (default 15)", @OA\Schema(type="integer", default=15)),
     *     @OA\Response(
     *         response=200,
     *         description="Card flags retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example=""),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CardFlag")),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=42)
     *             ),
     *             @OA\Property(property="errors", type="string", nullable=true)
     *         )
     *     )
     * )
     */
    public function index(CardFlagFilterRequest $request): ResponseResource
    {
        $filters = $request->validated();

        $query = CardFlag::query();

        if (!empty($filters['id'])) {
            $query->where('id', $filters['id']);
        }

        if (!empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }

        if (!empty($filters['created_at'])) {
            $query->whereDate('created_at', $filters['created_at']);
        }

        if (!empty($filters['updated_at'])) {
            $query->whereDate('updated_at', $filters['updated_at']);
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
     *     path="/api/v1/card-flags/{id}",
     *     summary="Update a card flag name - ADM",
     *     description="Updates the name of an existing card flag.",
     *     operationId="updateCardFlag",
     *     tags={"Card Flags"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Card flag ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Mastercard Updated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Card flag updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Card flag updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/CardFlag"),
     *             @OA\Property(property="errors", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Card flag not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Card flag not found."),
     *             @OA\Property(property="errors", type="string", example="No query results for model ...")
     *         )
     *     )
     * )
     */
    public function update(array $data, int $id): ResponseResource
    {
        try {
            $flag = CardFlag::findOrFail($id);
            $flag->update(['name' => $data['name']]);

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'Card flag updated successfully.',
                'data' => $flag,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to update card flag', ['id' => $id, 'exception' => $e]);
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to update card flag.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/card-flags/{id}/image",
     *     summary="Update card flag image - ADM",
     *     description="Replace the image (PNG/SVG) for a specific card flag.",
     *     operationId="updateCardFlagImage",
     *     tags={"Card Flags"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Card flag ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"photo"},
     *                 @OA\Property(
     *                     property="photo",
     *                     type="string",
     *                     format="binary",
     *                     description="New PNG or SVG image file (max 2MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Image updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Card flag image updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/CardFlag"),
     *             @OA\Property(property="errors", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Card flag not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Card flag not found."),
     *             @OA\Property(property="errors", type="string", example="No query results for model ...")
     *         )
     *     )
     * )
     */
    public function updateImage(CardFlagUpdateImageRequest $request, int $id): ResponseResource
    {
        try {
            DB::beginTransaction();

            $flag = CardFlag::findOrFail($id);

            // Remove old image
            if ($flag->photo_path && Storage::disk('public')->exists($flag->photo_path)) {
                Storage::disk('public')->delete($flag->photo_path);
            }

            // Store new image
            $path = $request->file('photo')->store('card_flags', 'public');
            $flag->update(['photo_path' => $path]);

            DB::commit();

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'Card flag image updated successfully.',
                'data' => $flag,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to update card flag image', [
                'card_flag_id' => $id,
                'exception' => $e
            ]);

            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to update card flag image.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/card-flags/{cardFlag}",
     *     summary="Delete a card flag - ADM",
     *     description="Deletes a card flag and removes its associated image from storage.",
     *     operationId="destroyCardFlag",
     *     tags={"Card Flags"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="cardFlag",
     *         in="path",
     *         required=true,
     *         description="Card Flag ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Card flag deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Card flag deleted successfully."),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Card flag not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Card flag not found."),
     *             @OA\Property(property="errors", type="string", example="No query results for model ...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Failed to delete card flag."),
     *             @OA\Property(property="errors", type="string", example="SQLSTATE...")
     *         )
     *     )
     * )
     */
    public function destroy(int $cardFlagId): ResponseResource
    {
        try {
            DB::beginTransaction();

            $flag = CardFlag::findOrFail($cardFlagId);

            if ($flag->photo_path && Storage::disk('public')->exists($flag->photo_path)) {
                Storage::disk('public')->delete($flag->photo_path);
            }

            $flag->delete();

            DB::commit();

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'Card flag deleted successfully.',
                'data' => null,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to delete card flag', [
                'card_flag_id' => $cardFlagId,
                'exception' => $e
            ]);

            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to delete card flag.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/card-flags/{id}/image",
     *     summary="Delete card flag image - ADM",
     *     description="Removes only the image of a card flag, without deleting the record.",
     *     operationId="deleteCardFlagImage",
     *     tags={"Card Flags"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Card flag ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Image deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Card flag image deleted successfully."),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Card flag not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Card flag not found."),
     *             @OA\Property(property="errors", type="string", example="No query results for model ...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Failed to delete card flag image."),
     *             @OA\Property(property="errors", type="string", example="SQLSTATE...")
     *         )
     *     )
     * )
     */
    public function deleteImage(int $id): ResponseResource
    {
        try {
            $flag = CardFlag::findOrFail($id);

            if ($flag->photo_path && Storage::disk('public')->exists($flag->photo_path)) {
                Storage::disk('public')->delete($flag->photo_path);
            }

            $flag->update(['photo_path' => null]);

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'Card flag image deleted successfully.',
                'data' => null,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to delete card flag image', [
                'card_flag_id' => $id,
                'exception' => $e
            ]);

            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to delete card flag image.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }
}
