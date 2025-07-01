<?php

namespace App\Services\Api\V1;

use App\Http\Requests\Api\V1\SolutionFilterRequest;
use App\Http\Requests\Api\V1\SolutionUpdateRequest;
use App\Http\Requests\Api\V1\StoreFilterRequest;
use App\Http\Requests\Api\V1\StoreStoreRequest;
use App\Http\Resources\Api\V1\ResponseResource;
use App\Models\Api\V1\Solution;
use App\Models\Api\V1\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class SolutionService
{
    /**
     * @OA\Post(
     *     path="/api/v1/solutions",
     *     summary="Create a new solution - ADM",
     *     description="Creates a new solution entry with an associated image file.",
     *     operationId="createSolution",
     *     tags={"Solutions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "description"},
     *                 @OA\Property(property="title", type="string", maxLength=255, example="Consigned Loan"),
     *                 @OA\Property(property="description", type="string", maxLength=255, example="Loan deducted from payroll"),
     *                 @OA\Property(property="photo", type="string", format="binary", description="Image file (max 2MB, jpeg/png/webp)"),
     *                 @OA\Property(property="order", type="integer", example=1),
     *                 @OA\Property(property="width", type="string", maxLength=2, example="Icon display width"),
     *                 @OA\Property(property="height", type="string", maxLength=2, example="Icon display height"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Solution created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=201),
     *             @OA\Property(property="message", type="string", example="Solution created successfully."),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/Solution"),
     *             @OA\Property(property="errors", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Failed to create solution."),
     *             @OA\Property(property="errors", type="string", example="SQLSTATE...")
     *         )
     *     )
     * )
     */
    public function store(Request $request): ResponseResource
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'file', 'mimes:png,svg', 'max:2048'],
            'order' => ['nullable', 'integer', 'min:0'],
            'width' => ['required', 'string'],
            'height' => ['required', 'string'],
        ]);

        try {
            DB::beginTransaction();

            $path = $request->file('photo')?->store('solution_photos', 'public');
            // [$width, $height] = getimagesize($request->file('photo')->getRealPath());

            $solution = Solution::create([
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'photo_path' => $path,
                'order' => $request->input('order'),
                'width' => $request->input('width'),
                'height' => $request->input('height'),
            ]);

            DB::commit();

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_CREATED,
                'message' => 'Solution created successfully.',
                'data' => $solution,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to create solution', ['exception' => $e]);
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to create solution.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/v1/solutions",
     *     summary="List all solutions",
     *     description="Returns a paginated list of solutions with optional filters.",
     *     operationId="getSolutions",
     *     tags={"Solutions"},
     *     @OA\Parameter(name="id", in="query", required=false, description="Filter by ID", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="title", in="query", required=false, description="Filter by title", @OA\Schema(type="string")),
     *     @OA\Parameter(name="description", in="query", required=false, description="Filter by description", @OA\Schema(type="string")),
     *     @OA\Parameter(name="created_at", in="query", required=false, description="Filter by creation date", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="updated_at", in="query", required=false, description="Filter by update date", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page (default 15)", @OA\Schema(type="integer", default=15)),
     *     @OA\Response(
     *         response=200,
     *         description="List of solutions retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example=""),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Solution")),
     *             ),
     *             @OA\Property(property="errors", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function index(SolutionFilterRequest $filters): ResponseResource
    {
        $filters = $filters->validated();
        $query = Solution::query();

        if (!empty($filters['id'])) {
            $query->where('id', $filters['id']);
        }

        if (!empty($filters['title'])) {
            $query->where('title', 'like', '%' . $filters['title'] . '%');
        }

        if (!empty($filters['description'])) {
            $query->where('description', 'like', '%' . $filters['description'] . '%');
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
     *     path="/api/v1/solutions/{id}",
     *     summary="Update a solution - ADM",
     *     description="Updates the title, description, width, and height of a solution.",
     *     operationId="updateSolution",
     *     tags={"Solutions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true, description="Solution ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "description", "width", "height"},
     *             @OA\Property(property="title", type="string", example="Updated Title"),
     *             @OA\Property(property="description", type="string", example="Updated Description"),
     *             @OA\Property(property="order", type="integer", example=1),
     *             @OA\Property(property="width", type="string", example="1920"),
     *             @OA\Property(property="height", type="string", example="1080")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Solution updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Solution updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Solution"),
     *             @OA\Property(property="errors", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Solution not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Solution not found."),
     *             @OA\Property(property="errors", type="string", example="No query results for model...")
     *         )
     *     )
     * )
     */
    public function update(SolutionUpdateRequest $request, int $id): ResponseResource
    {
        try {
            DB::beginTransaction();

            $solution = Solution::findOrFail($id);

            $solution->update($request->validated());

            DB::commit();

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'Solution updated successfully.',
                'data' => $solution,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to update solution', [
                'solution_id' => $id,
                'exception' => $e
            ]);

            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to update solution. Please try again later.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/solutions/{id}/photo",
     *     summary="Update solution photo - ADM",
     *     description="Updates only the photo of a given solution. Max 2MB (jpeg/png/webp).",
     *     operationId="updateSolutionPhoto",
     *     tags={"Solutions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Solution ID", @OA\Schema(type="integer", example=1)),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"photo"},
     *                 @OA\Property(
     *                     property="photo",
     *                     type="string",
     *                     format="binary",
     *                     description="New image file (jpeg/png/webp, max 2MB)"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Photo updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Solution photo updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Solution"),
     *             @OA\Property(property="errors", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Solution not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Solution not found."),
     *             @OA\Property(property="errors", type="string", example="No query results for model...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function updatePhoto(Request $request, int $id): ResponseResource
    {
        $request->validate([
            'photo' => ['required', 'file', 'mimes:png,svg', 'max:2048'],
        ]);

        try {
            DB::beginTransaction();

            $solution = Solution::findOrFail($id);

            if ($solution->photo_path && Storage::disk('public')->exists($solution->photo_path)) {
                Storage::disk('public')->delete($solution->photo_path);
            }

            $newPath = $request->file('photo')->store('solution_photos', 'public');

            $solution->update([
                'photo_path' => $newPath,
            ]);

            DB::commit();

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'Solution photo updated successfully.',
                'data' => $solution,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Failed to update solution photo', [
                'solution_id' => $id,
                'exception' => $e
            ]);

            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to update solution photo. Please try again later.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/solutions/{id}/photo",
     *     summary="Delete solution photo - ADM",
     *     description="Deletes the photo of a given solution without removing the solution itself.",
     *     operationId="deleteSolutionPhoto",
     *     tags={"Solutions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Solution ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Photo deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Solution photo deleted successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Solution"),
     *             @OA\Property(property="errors", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Solution not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Solution not found."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="errors", type="string", example="No query results for model...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Failed to delete solution photo."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="errors", type="string", example="SQLSTATE...")
     *         )
     *     )
     * )
     */
    public function deletePhoto(int $id): ResponseResource
    {
        try {
            DB::beginTransaction();

            $solution = Solution::findOrFail($id);

            if ($solution->photo_path && Storage::disk('public')->exists($solution->photo_path)) {
                Storage::disk('public')->delete($solution->photo_path);
            }

            $solution->update(['photo_path' => null]);

            DB::commit();

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'Solution photo deleted successfully.',
                'data' => $solution,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Failed to delete solution photo', [
                'solution_id' => $id,
                'exception' => $e,
            ]);

            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to delete solution photo. Please try again later.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/solutions/{id}",
     *     summary="Delete solution - ADM",
     *     description="Deletes a solution and its associated photo file if it exists.",
     *     operationId="deleteSolution",
     *     tags={"Solutions"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Solution ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Solution deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Solution deleted successfully."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Solution not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Solution not found."),
     *             @OA\Property(property="errors", type="string", example="No query results for model...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Failed to delete solution."),
     *             @OA\Property(property="errors", type="string", example="SQLSTATE...")
     *         )
     *     )
     * )
     */
    public function destroy(int $id): ResponseResource
    {
        try {
            DB::beginTransaction();

            $solution = Solution::findOrFail($id);

            if ($solution->photo_path && Storage::disk('public')->exists($solution->photo_path)) {
                Storage::disk('public')->delete($solution->photo_path);
            }

            $solution->delete();

            DB::commit();

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'Solution deleted successfully.',
                'data' => null,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Failed to delete solution', [
                'solution_id' => $id,
                'exception' => $e,
            ]);

            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to delete solution. Please try again later.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }
}
