<?php

namespace App\Services\Api\V1;

use App\Http\Requests\Api\V1\StoreFilterRequest;
use App\Http\Requests\Api\V1\StoreStoreRequest;
use App\Http\Requests\Api\V1\TestimonyApproveRequest;
use App\Http\Requests\Api\V1\TestimonyDestroyRequest;
use App\Http\Requests\Api\V1\TestimonyFilterRequest;
use App\Http\Requests\Api\V1\TestimonyRejectRequest;
use App\Http\Requests\Api\V1\TestimonyStoreRequest;
use App\Http\Requests\Api\V1\TestimonyUpdatePhotoRequest;
use App\Http\Requests\Api\V1\TestimonyUpdateRequest;
use App\Http\Resources\Api\V1\ResponseResource;
use App\Models\Api\V1\Store;
use App\Models\Api\V1\Testimony;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class TestimonyService
{

    /**
     * @OA\Get(
     *     path="/api/v1/testimonies",
     *     summary="List testimonies with optional filters and pagination",
     *     description="Returns a paginated list of testimonies. Supports filtering by id, name, surname, city, and uf.",
     *     operationId="getTestimonies",
     *     tags={"Testimonies"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=false,
     *         description="Filter by testimony ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=false,
     *         description="Filter by first name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="surname",
     *         in="query",
     *         required=false,
     *         description="Filter by last name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="city",
     *         in="query",
     *         required=false,
     *         description="Filter by city",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="uf",
     *         in="query",
     *         required=false,
     *         description="Filter by state abbreviation (UF)",
     *         @OA\Schema(type="string", maxLength=2)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Filter by status",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         required=false,
     *         description="Sort order",
     *         @OA\Schema(
     *             type="string",
     *             enum={"ASC", "DESC"}
     *         ),
     *         example="DESC"
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         required=false,
     *         description="Field to sort by",
     *         @OA\Schema(
     *             type="string",
     *             enum={"id", "name", "surname", "city", "uf", "status"}
     *         ),
     *         example="id"
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Items per page (default: 15, max: 100)",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with paginated testimonies",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example=""),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(ref="#/components/schemas/Testimony")
     *                 ),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=45)
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function index(TestimonyFilterRequest $request): ResponseResource
    {
        $query = Testimony::query();

        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('surname')) {
            $query->where('surname', 'like', '%' . $request->surname . '%');
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if ($request->filled('uf')) {
            $query->where('uf', $request->uf);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->boolean('status'));
        }

        $perPage = $request->input('per_page', 15);

        if ($request->filled('sort_order')) {
            $sort_by = $request->input('sort_by', 'id');
            $sort_order = $request->input('sort_order', 'DESC');
            $query->orderBy($sort_by, $sort_order);
        }

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => '',
            'data' => $query->paginate($perPage),
            'errors' => null,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/testimonies",
     *     summary="Create a new testimony",
     *     description="Creates a new testimony with a photo and message.",
     *     operationId="storeTestimony",
     *     tags={"Testimonies"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Testimony data with photo upload",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "surname", "city", "uf", "message"},
     *                 @OA\Property(property="name", type="string", maxLength=255, example="Maria"),
     *                 @OA\Property(property="surname", type="string", maxLength=255, example="Silva"),
     *                 @OA\Property(property="city", type="string", maxLength=255, example="Recife"),
     *                 @OA\Property(property="uf", type="string", maxLength=2, example="PE"),
     *                 @OA\Property(property="message", type="string", example="Excellent service and very helpful."),
     *                 @OA\Property(
     *                     property="photo",
     *                     type="string",
     *                     format="binary",
     *                     description="Photo file (JPEG, PNG or WEBP up to 2MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Testimony created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=201),
     *             @OA\Property(property="message", type="string", example="Testimony created successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Testimony"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to create testimony",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Failed to create testimony."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="string", example="Internal server error.")
     *         )
     *     )
     * )
     */
    public function store(TestimonyStoreRequest $request): ResponseResource
    {
        try {
            DB::beginTransaction();
            $validatedData = $request->validated();

            $path = false;
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('testimony_photos', 'public');
            }
            if ($path) {
                $validatedData['photo_path'] = $path;
            }

            $testimony = Testimony::create($validatedData);

            DB::commit();

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_CREATED,
                'message' => 'Testimony created successfully.',
                'data' => $testimony,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to create testimony', ['exception' => $e]);

            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to create testimony.',
                'data' => null,
                'errors' => config('app.debug') ? $e->getMessage() : 'Internal server error.',
            ]);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/testimonies/{id}",
     *     summary="Update an existing testimony",
     *     description="Updates testimony details such as name, surname, city, uf, and message.",
     *     operationId="updateTestimony",
     *     tags={"Testimonies"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the testimony to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Fields to update in the testimony",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", maxLength=255, example="João"),
     *             @OA\Property(property="surname", type="string", maxLength=255, example="Pereira"),
     *             @OA\Property(property="city", type="string", maxLength=255, example="Palmares"),
     *             @OA\Property(property="uf", type="string", maxLength=2, example="PE"),
     *             @OA\Property(property="message", type="string", example="This service really helped me.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Testimony updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Testimony updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Testimony"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Testimony not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Testimony not found."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="string", example="Resource not found.")
     *         )
     *     )
     * )
     */
    public function update(int $id, TestimonyUpdateRequest $request): ResponseResource
    {
        $testimony = Testimony::findOrFail($id);

        $testimony->update($request->only([
            'name',
            'surname',
            'city',
            'uf',
            'message',
        ]));

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'Testimony updated successfully.',
            'data' => $testimony,
            'errors' => null,
        ]);
    }


    /**
     * @OA\Post(
     *     path="/api/v1/testimonies/{id}/photo",
     *     summary="Update the photo of a testimony",
     *     description="Updates the photo of a testimony by replacing the existing image with a new one.",
     *     operationId="updateTestimonyPhoto",
     *     tags={"Testimonies"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the testimony whose photo will be updated",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="New photo to upload (JPEG, PNG or WEBP)",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"photo"},
     *                 @OA\Property(
     *                     property="photo",
     *                     type="string",
     *                     format="binary",
     *                     description="New image file (max 2MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Photo updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Photo updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Testimony"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Testimony not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Testimony not found."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="string", example="Resource not found.")
     *         )
     *     )
     * )
     */
    public function updatePhoto(int $id, TestimonyUpdatePhotoRequest $request): ResponseResource
    {
        $testimony = Testimony::findOrFail($id);

        if ($testimony->photo_path && Storage::disk('public')->exists($testimony->photo_path)) {
            Storage::disk('public')->delete($testimony->photo_path);
        }

        $photo = $request->file('photo');
        throw_if(!$photo->isValid(), \RuntimeException::class, 'Invalid photo upload.');

        $newPath = $photo->store('testimony_photos', 'public');

        $testimony->update(['photo_path' => $newPath]);

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'Photo updated successfully.',
            'data' => $testimony,
            'errors' => null,
        ]);
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/testimonies/{id}/photo",
     *     summary="Delete a testimony photo",
     *     description="Deletes the photo associated with a testimony and updates the record.",
     *     operationId="deleteTestimonyPhoto",
     *     tags={"Testimonies"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the testimony whose photo will be deleted",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Photo deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Photo deleted successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Testimony"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Testimony not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Testimony not found."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="string", example="Resource not found.")
     *         )
     *     )
     * )
     */
    public function deletePhoto(int $id): ResponseResource
    {
        $testimony = Testimony::findOrFail($id);

        $photoPath = $testimony->photo_path;

        if ($photoPath && Storage::disk('public')->exists($photoPath)) {
            Storage::disk('public')->delete($photoPath);
        }

        $testimony->update(['photo_path' => null]);

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'Photo deleted successfully.',
            'data' => $testimony,
            'errors' => null,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/testimonies/{id}",
     *     summary="Delete a testimony",
     *     description="Deletes a testimony and its associated photo from storage.",
     *     operationId="deleteTestimony",
     *     tags={"Testimonies"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the testimony to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Testimony deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Testimony deleted successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1)
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Testimony not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Testimony not found."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="string", example="Resource not found.")
     *         )
     *     )
     * )
     */
    public function destroy(int $id, TestimonyDestroyRequest $request): ResponseResource
    {
        $testimony = Testimony::findOrFail($id);

        $photoPath = $testimony->photo_path;

        if ($photoPath && Storage::disk('public')->exists($photoPath)) {
            Storage::disk('public')->delete($photoPath);
        }

        $testimony->delete();

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'Testimony deleted successfully.',
            'data' => ['id' => $id],
            'errors' => null,
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/testimonies/{id}/approve",
     *     summary="Approve a testimony",
     *     description="Sets the status of the testimony to true (approved).",
     *     operationId="approveTestimony",
     *     tags={"Testimonies"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the testimony to approve",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Testimony approved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Testimony approved successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Testimony"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Testimony not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Testimony not found."),
     *             @OA\Property(property="errors", type="string", example="Resource not found."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function approve(int $id, TestimonyApproveRequest $request): ResponseResource
    {
        $testimony = Testimony::findOrFail($id);
        $testimony->update(['status' => true]);

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'Testimony approved successfully.',
            'data' => $testimony,
            'errors' => null,
        ]);
    }


    /**
     * @OA\Patch(
     *     path="/api/v1/testimonies/{id}/reject",
     *     summary="Reject a testimony",
     *     description="Sets the status of the testimony to false (rejected).",
     *     operationId="rejectTestimony",
     *     tags={"Testimonies"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the testimony to reject",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Testimony rejected successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Testimony rejected successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Testimony"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Testimony not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Testimony not found."),
     *             @OA\Property(property="errors", type="string", example="Resource not found."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function reject(int $id, TestimonyRejectRequest $request): ResponseResource
    {
        $testimony = Testimony::findOrFail($id);
        $testimony->update(['status' => false]);

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'Testimony rejected successfully.',
            'data' => $testimony,
            'errors' => null,
        ]);
    }
}
