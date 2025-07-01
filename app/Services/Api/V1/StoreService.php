<?php

namespace App\Services\Api\V1;

use App\Http\Requests\Api\V1\StoreFilterRequest;
use App\Http\Requests\Api\V1\StoreStoreRequest;
use App\Http\Resources\Api\V1\ResponseResource;
use App\Models\Api\V1\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class StoreService
{


    /**
     * @OA\Post(
     *     path="/api/v1/stores",
     *     summary="Create a new store - ADM",
     *     description="Creates a new store with a single address and multiple photos.",
     *     operationId="createStore",
     *     tags={"Stores"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Store creation payload",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={
     *                     "name",
     *                     "photos[]",
     *                     "address[uf]",
     *                     "address[city]",
     *                     "address[neighborhood]",
     *                     "address[zip_code]",
     *                     "address[street]",
     *                     "address[number]",
     *                     "address[coordinates]"
     *                 },
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     maxLength=255,
     *                     example="AuthLend - Unidade Centro"
     *                 ),
     *                 @OA\Property(
     *                     property="photos[]",
     *                     type="array",
     *                     description="Array of up to 5 store photo files",
     *                     @OA\Items(
     *                         type="string",
     *                         format="binary"
     *                     )
     *                 ),
     *                 @OA\Property(property="address[uf]", type="string", minLength=2, maxLength=2, example="PE"),
     *                 @OA\Property(property="address[city]", type="string", maxLength=100, example="Recife"),
     *                 @OA\Property(property="address[neighborhood]", type="string", maxLength=100, example="Boa Viagem"),
     *                 @OA\Property(property="address[zip_code]", type="string", pattern="^\d{5}-?\d{3}$", example="51020-000"),
     *                 @OA\Property(property="address[street]", type="string", maxLength=255, example="Avenida Boa Viagem"),
     *                 @OA\Property(property="address[number]", type="string", maxLength=20, example="1234"),
     *                 @OA\Property(property="address[coordinates]", type="string", pattern="^-?\d+\.\d+,\s?-?\d+\.\d+$", example="-8.127690,-34.899860")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Store created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Store created successfully."),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/Store"),
     *             @OA\Property(property="errors", type="string", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="data", type="object", example=null),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 additionalProperties=@OA\Property(type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to create store. Please try again later."),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="string", example="SQLSTATE[23000]: Integrity constraint violation...")
     *         )
     *     )
     * )
     */
    public function store(StoreStoreRequest $request): ResponseResource
    {
        $validatedData = $request->validated();
        try {
            DB::beginTransaction();

            $store = Store::create([
                'name' => $validatedData['name'],
            ]);

            $addressData = $validatedData['address'];
            $store->address()->create($addressData);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photoFile) {
                    $path = $photoFile->store('store_photos', 'public');
                    $store->photoStores()->create(['photo_path' => $path]);
                }
            }

            DB::commit();

            $store->load(['address', 'photoStores']);

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_CREATED,
                'message' => 'Store created successfully.',
                'data' => $store,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to create store: ' . $e->getMessage(), ['exception' => $e]);
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to create store. Please try again later.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/stores",
     *     summary="List paginated stores",
     *     tags={"Stores"},
     *     @OA\Parameter(name="id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="name", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="uf", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="city", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="neighborhood", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="zip_code", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="street", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="number", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="coordinates", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="include_default", in="query", required=false, @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="created_at", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="updated_at", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of stores",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example=""),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Store")),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=75)
     *             ),
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"), nullable=true)
     *         )
     *     )
     * )
     */
    public function index(StoreFilterRequest $filters): ResponseResource
    {
        $filters = $filters->validated();

        $query = Store::with(['address', 'photoStores']);

        if (!($filters['include_default'] ?? false)) {
            $query->where('name', '!=', 'Todas');
        }

        if (!empty($filters['id'])) {
            $query->where('id', $filters['id']);
        }

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        $query->when($filters['uf'] ?? null, function ($q, $value) {
            $q->whereHas('address', fn($q) => $q->where('uf', $value));
        });

        foreach (['city', 'neighborhood', 'zip_code', 'street', 'coordinates'] as $field) {
            if (!empty($filters[$field])) {
                $query->whereHas('address', fn($q) => $q->where($field, 'like', '%' . $filters[$field] . '%'));
            }
        }

        if (!empty($filters['number'])) {
            $query->whereHas('address', fn($q) => $q->where('number', $filters['number']));
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
     * @OA\Post(
     *     path="/api/v1/stores/{store}/photos/{photo}",
     *     summary="Update a store photo - ADM",
     *     description="Updates an existing store photo with a new image file.",
     *     operationId="updateStorePhoto",
     *     tags={"Stores"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="store", in="path", required=true, description="Store ID", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="photo", in="path", required=true, description="Photo ID", @OA\Schema(type="integer", example=3)),
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
     *                     description="New image file (max 2MB, jpeg/png/webp)"
     *                 ),
     *                 @OA\Property(property="_method", type="string", example="PUT", description="Do not change this value. Required by Laravel to simulate a PUT request."),
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
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/PhotoStore"),
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
     *         response=404,
     *         description="Store or photo not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Store or photo not found."),
     *             @OA\Property(property="errors", type="string", example="No query results for model ...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Failed to update photo."),
     *             @OA\Property(property="errors", type="string", example="SQLSTATE...")
     *         )
     *     )
     * )
     */
    public function updatePhoto(int $storeId, int $photoId, Request $request): ResponseResource
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);
        try {
            DB::beginTransaction();
            $store = Store::findOrFail($storeId);
            $photo = $store->photoStores()->findOrFail($photoId);
            if ($photo->photo_path && Storage::disk('public')->exists($photo->photo_path)) {
                Storage::disk('public')->delete($photo->photo_path);
            }
            $newPath = $request->file('photo')->store('store_photos', 'public');
            $photo->update([
                'photo_path' => $newPath,
            ]);
            DB::commit();
            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'Photo updated successfully.',
                'data' => $photo,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to update store photo', [
                'store_id' => $storeId,
                'photo_id' => $photoId,
                'exception' => $e
            ]);
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to update photo. Please try again later.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/stores/{store}/photos/{photo}",
     *     summary="Delete a store photo - ADM",
     *     description="Deletes a photo associated with the given store.",
     *     operationId="deleteStorePhoto",
     *     tags={"Stores"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="store", in="path", required=true, description="Store ID", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="photo", in="path", required=true, description="Photo ID", @OA\Schema(type="integer", example=3)),
     *     @OA\Response(
     *         response=200,
     *         description="Photo deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Photo deleted successfully."),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/PhotoStore"),
     *             @OA\Property(property="errors", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Store or photo not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Store or photo not found."),
     *             @OA\Property(property="errors", type="string", example="No query results for model ...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Failed to delete photo."),
     *             @OA\Property(property="errors", type="string", example="SQLSTATE...")
     *         )
     *     )
     * )
     */
    public function deletePhoto(int $storeId, int $photoId): ResponseResource
    {
        try {
            DB::beginTransaction();

            $store = Store::findOrFail($storeId);
            $photo = $store->photoStores()->findOrFail($photoId);

            if ($photo->photo_path && Storage::disk('public')->exists($photo->photo_path)) {
                Storage::disk('public')->delete($photo->photo_path);
            }

            $photo->delete();

            DB::commit();

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'Photo deleted successfully.',
                'data' => $photo,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Failed to delete store photo', [
                'store_id' => $storeId,
                'photo_id' => $photoId,
                'exception' => $e,
            ]);

            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to delete photo. Please try again later.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/stores/{store}/photos",
     *     summary="Add a new photo to a store - ADM",
     *     description="Adds a single photo to the specified store. A store can have up to 5 photos.",
     *     operationId="addStorePhoto",
     *     tags={"Stores"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="store", in="path", required=true, description="Store ID", @OA\Schema(type="integer", example=1)),
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
     *                     description="New image file (max 2MB, jpeg/png/webp)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Photo added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=201),
     *             @OA\Property(property="message", type="string", example="Photo added successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/PhotoStore"),
     *             @OA\Property(property="errors", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Photo limit reached",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="This store already has 5 photos."),
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
     *             @OA\Property(property="message", type="string", example="Failed to add photo."),
     *             @OA\Property(property="errors", type="string", example="SQLSTATE...")
     *         )
     *     )
     * )
     */
    public function addPhoto(int $storeId, Request $request): ResponseResource
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        try {
            DB::beginTransaction();

            $store = Store::findOrFail($storeId);

            if ($store->photoStores()->count() >= 5) {
                return new ResponseResource([
                    'status' => 'error',
                    'status_code' => Response::HTTP_BAD_REQUEST,
                    'message' => 'This store already has 5 photos.',
                    'data' => null,
                    'errors' => null,
                ]);
            }

            $photoPath = $request->file('photo')->store('store_photos', 'public');

            $photo = $store->photoStores()->create([
                'photo_path' => $photoPath,
            ]);

            DB::commit();

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_CREATED,
                'message' => 'Photo added successfully.',
                'data' => $photo,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Failed to add store photo', [
                'store_id' => $storeId,
                'exception' => $e,
            ]);

            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to add photo. Please try again later.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }


    /**
     * @OA\Put(
     *     path="/api/v1/stores/{store}",
     *     summary="Update store - ADM",
     *     description="Updates the store's basic information and its address (1:1 relationship).",
     *     operationId="updateStore",
     *     tags={"Stores"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(name="store", in="path", required=true, description="Store ID", @OA\Schema(type="integer", example=1)),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "address"},
     *                 @OA\Property(property="name", type="string", example="Updated Store Name"),
     *                 @OA\Property(property="address", type="object",
     *                     required={"uf", "city", "neighborhood", "zip_code", "street", "number", "coordinates"},
     *                     @OA\Property(property="uf", type="string", example="PE"),
     *                     @OA\Property(property="city", type="string", example="Recife"),
     *                     @OA\Property(property="neighborhood", type="string", example="Boa Viagem"),
     *                     @OA\Property(property="zip_code", type="string", example="51020-000"),
     *                     @OA\Property(property="street", type="string", example="Avenida Boa Viagem"),
     *                     @OA\Property(property="number", type="string", example="123"),
     *                     @OA\Property(property="coordinates", type="string", example="-8.127690,-34.899860")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Store updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Store updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Store"),
     *             @OA\Property(property="errors", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Store not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Store not found."),
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
     *             @OA\Property(property="message", type="string", example="Failed to update store."),
     *             @OA\Property(property="errors", type="string", example="SQLSTATE...")
     *         )
     *     )
     * )
     */
    public function update(int $storeId, Request $request): ResponseResource
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'array'],
            'address.uf' => ['required', 'string', 'size:2'],
            'address.city' => ['required', 'string', 'max:100'],
            'address.neighborhood' => ['required', 'string', 'max:100'],
            'address.zip_code' => ['required', 'string', 'regex:/^\d{5}-?\d{3}$/'],
            'address.street' => ['required', 'string', 'max:255'],
            'address.number' => ['required', 'string'],
            'address.coordinates' => ['required', 'string', 'regex:/-?\d+.\d+,\s?-?\d+.\d+/'],
        ]);

        try {
            DB::beginTransaction();

            $store = Store::findOrFail($storeId);
            $store->update(['name' => $validated['name']]);

            $store->address()->update($validated['address']);

            DB::commit();

            $store->load(['address', 'photoStores']);

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'Store updated successfully.',
                'data' => $store,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Failed to update store', [
                'store_id' => $storeId,
                'exception' => $e,
            ]);

            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to update store. Please try again later.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/stores/{store}",
     *     summary="Delete a store - ADM",
     *     description="Deletes a store along with its address and associated photos.",
     *     operationId="deleteStore",
     *     tags={"Stores"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="store",
     *         in="path",
     *         required=true,
     *         description="Store ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Store deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Store deleted successfully."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Store not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Store not found."),
     *             @OA\Property(property="errors", type="string", example="No query results for model...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Failed to delete store."),
     *             @OA\Property(property="errors", type="string", example="SQLSTATE...")
     *         )
     *     )
     * )
     */
    public function destroy(int $storeId): ResponseResource
    {
        try {
            DB::beginTransaction();

            $store = Store::with(['photoStores', 'address'])->findOrFail($storeId);

            foreach ($store->photoStores as $photo) {
                if ($photo->photo_path && Storage::disk('public')->exists($photo->photo_path)) {
                    Storage::disk('public')->delete($photo->photo_path);
                }
            }

            $store->photoStores()->delete();
            $store->address()->delete();
            $store->delete();

            DB::commit();

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'Store deleted successfully.',
                'data' => null,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to delete store', [
                'store_id' => $storeId,
                'exception' => $e
            ]);
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to delete store.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }
}
