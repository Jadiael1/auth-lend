<?php

namespace App\Services\Api\V1;

use App\Http\Resources\Api\V1\ResponseResource;
use App\Models\Api\V1\Announcement;
use App\Models\Api\V1\ValueType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class AnnouncementService
{
    /**
     * @OA\Post(
     *     path="/api/v1/announcements",
     *     summary="Create a new announcement",
     *     description="Creates a new announcement. Allows optional photo upload.",
     *     tags={"Announcements"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "message"},
     *                 @OA\Property(property="title", type="string", maxLength=255, example="Special Promotion"),
     *                 @OA\Property(property="subtitle", type="string", maxLength=255, example="Limited time offer"),
     *                 @OA\Property(property="message", type="string", example="Enjoy our special promotion today only!"),
     *                 @OA\Property(property="button_text", type="string", maxLength=100, example="Shop Now"),
     *                 @OA\Property(property="button_url", type="string", format="url", example="https://example.com/shop"),
     *                 @OA\Property(property="note", type="string", example="Terms and conditions apply."),
     *                 @OA\Property(property="order", type="integer", example=1),
     *                 @OA\Property(property="status", type="boolean", example=true),
     *                 @OA\Property(property="photo", type="string", format="binary", description="Photo file (png, jpg, jpeg, webp)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Announcement created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=201),
     *             @OA\Property(property="message", type="string", example="Announcement created successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Announcement"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="errors", type="object"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function store(Request $request): ResponseResource
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'button_url' => ['nullable', 'url'],
            'note' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
            'status' => ['nullable', 'boolean'],
            'photo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Validation failed.',
                'data' => null,
                'errors' => $validator->errors(),
            ]);
        }

        $validatedData = $validator->validated();
        $path = false;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('announcement_photos', 'public');
        }
        if ($path) {
            $validatedData['photo_path'] = $path;
        }

        $validatedData['status'] = false;

        $announcement = Announcement::create($validatedData);

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_CREATED,
            'message' => 'Announcement created successfully.',
            'data' => $announcement,
            'errors' => null,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/announcements",
     *     summary="List announcements",
     *     description="Returns a paginated list of announcements with optional filters.",
     *     operationId="listAnnouncements",
     *     tags={"Announcements"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         required=false,
     *         description="Filter by announcement ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         required=false,
     *         description="Filter by announcement title (partial match)",
     *         @OA\Schema(type="string", example="Promo")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Filter by active status",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         required=false,
     *         description="Field to sort by",
     *         @OA\Schema(
     *             type="string",
     *             enum={"id", "title", "subtitle", "message", "button_text", "button_url", "note", "order", "status", "photo_path", "created_at", "updated_at"}
     *         ),
     *         example="id"
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
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of items per page (default: 15)",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of announcements",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example=""),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(ref="#/components/schemas/Announcement")
     *                 ),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=45)
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="errors", type="object"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function index(Request $request): ResponseResource
    {
        $validator = Validator::make($request->all(), [
            'id' => ['nullable', 'integer', 'exists:announcements,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:true,false,1,0'],
            'sort_order' => ['nullable', 'string', 'max:4'],
            'sort_by' => ['nullable', 'string', 'max:20'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Validation failed.',
                'data' => null,
                'errors' => $validator->errors(),
            ]);
        }

        $query = Announcement::query();

        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
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
     * @OA\Put(
     *     path="/api/v1/announcements/{id}",
     *     summary="Update an announcement",
     *     description="Updates an existing announcement (excluding photo).",
     *     operationId="updateAnnouncement",
     *     tags={"Announcements"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the announcement to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Title"),
     *             @OA\Property(property="subtitle", type="string", example="Updated Subtitle"),
     *             @OA\Property(property="message", type="string", example="Updated main message."),
     *             @OA\Property(property="button_text", type="string", example="Click Here"),
     *             @OA\Property(property="button_url", type="string", format="url", example="https://example.com/cta"),
     *             @OA\Property(property="note", type="string", example="Final note."),
     *             @OA\Property(property="order", type="integer", example=2),
     *             @OA\Property(property="status", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Announcement updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Announcement updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Announcement"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="errors", type="object"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Announcement not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Announcement not found."),
     *             @OA\Property(property="errors", type="string", example="Resource not found."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function update(int $id, Request $request): ResponseResource
    {
        $announcement = Announcement::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => ['sometimes', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'message' => ['sometimes', 'string'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'button_url' => ['nullable', 'url'],
            'note' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
            'status' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Validation failed.',
                'data' => null,
                'errors' => $validator->errors(),
            ]);
        }

        $announcement->update($validator->validated());

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'Announcement updated successfully.',
            'data' => $announcement,
            'errors' => null,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/announcements/{id}/photo",
     *     summary="Update announcement photo",
     *     description="Updates the photo of an announcement by replacing the existing image with a new one.",
     *     operationId="updateAnnouncementPhoto",
     *     tags={"Announcements"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the announcement to update the photo",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="New photo file (JPEG, PNG, or WEBP, max 2MB)",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"photo"},
     *                 @OA\Property(
     *                     property="photo",
     *                     type="string",
     *                     format="binary",
     *                     description="New image file"
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
     *             @OA\Property(property="data", ref="#/components/schemas/Announcement"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="errors", type="object"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Announcement not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Announcement not found."),
     *             @OA\Property(property="errors", type="string", example="Resource not found."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function updatePhoto(int $id, Request $request): ResponseResource
    {
        $validator = Validator::make($request->all(), [
            'photo' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:2048'], // 2MB
        ]);

        if ($validator->fails()) {
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'Validation failed.',
                'data' => null,
                'errors' => $validator->errors(),
            ]);
        }

        $announcement = Announcement::findOrFail($id);

        if ($announcement->photo_path && Storage::disk('public')->exists($announcement->photo_path)) {
            Storage::disk('public')->delete($announcement->photo_path);
        }

        $photo = $request->file('photo');
        throw_if(!$photo->isValid(), \RuntimeException::class, 'Invalid photo upload.');

        $newPath = $photo->store('announcement_photos', 'public');

        $announcement->update(['photo_path' => $newPath]);

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'Photo updated successfully.',
            'data' => $announcement,
            'errors' => null,
        ]);
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/announcements/{id}/photo",
     *     summary="Delete announcement photo",
     *     description="Removes the photo associated with an announcement and updates the record.",
     *     operationId="deleteAnnouncementPhoto",
     *     tags={"Announcements"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the announcement whose photo will be deleted",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Photo deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Photo deleted successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Announcement"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Announcement not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Announcement not found."),
     *             @OA\Property(property="errors", type="string", example="Resource not found."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function deletePhoto(int $id): ResponseResource
    {
        $announcement = Announcement::findOrFail($id);

        $photoPath = $announcement->photo_path;

        if ($photoPath && Storage::disk('public')->exists($photoPath)) {
            Storage::disk('public')->delete($photoPath);
        }

        $announcement->update(['photo_path' => null]);

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'Photo deleted successfully.',
            'data' => $announcement,
            'errors' => null,
        ]);
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/announcements/{id}",
     *     summary="Delete an announcement",
     *     description="Deletes an announcement and its associated photo from the system.",
     *     operationId="deleteAnnouncement",
     *     tags={"Announcements"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the announcement to delete",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Announcement deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Announcement deleted successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1)
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Announcement not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Announcement not found."),
     *             @OA\Property(property="errors", type="string", example="Resource not found."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function remove(int $id): ResponseResource
    {
        $announcement = Announcement::findOrFail($id);

        $photoPath = $announcement->photo_path;

        if ($photoPath && Storage::disk('public')->exists($photoPath)) {
            Storage::disk('public')->delete($photoPath);
        }

        $announcement->delete();

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'Announcement deleted successfully.',
            'data' => ['id' => $id],
            'errors' => null,
        ]);
    }


    /**
     * @OA\Patch(
     *     path="/api/v1/announcements/{id}/activate",
     *     summary="Activate an announcement",
     *     description="Sets the status of the announcement to true (activated).",
     *     operationId="activateAnnouncement",
     *     tags={"Announcements"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the announcement to activate",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Announcement activated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Announcement activated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Announcement"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Announcement not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Announcement not found."),
     *             @OA\Property(property="errors", type="string", example="Resource not found."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function activeAd(int $id): ResponseResource
    {
        $announcement = Announcement::findOrFail($id);

        $announcement->update(['status' => true]);

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'Announcement activated successfully.',
            'data' => $announcement,
            'errors' => null,
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/announcements/{id}/disable",
     *     summary="Disable an announcement",
     *     description="Sets the status of the announcement to false (disabled).",
     *     operationId="disableAnnouncement",
     *     tags={"Announcements"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the announcement to disable",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Announcement disabled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Announcement disabled successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Announcement"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Announcement not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Announcement not found."),
     *             @OA\Property(property="errors", type="string", example="Resource not found."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function disableAd(int $id): ResponseResource
    {
        $announcement = Announcement::findOrFail($id);

        $announcement->update(['status' => false]);

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'Announcement disabled successfully.',
            'data' => $announcement,
            'errors' => null,
        ]);
    }
}
