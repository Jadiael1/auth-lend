<?php

namespace App\Services\Api\V1;

use App\Http\Requests\Api\V1\ClientStoreRequest;
use App\Http\Requests\Api\V1\ClientUpdateCpfBackPhotoRequest;
use App\Http\Requests\Api\V1\ClientUpdateCpfFrontPhotoRequest;
use App\Http\Requests\Api\V1\ClientUpdateRequest;
use App\Http\Requests\Api\V1\ClientUpdateRgBackPhotoRequest;
use App\Http\Requests\Api\V1\ClientUpdateRgFrontPhotoRequest;
use App\Http\Resources\Api\V1\ResponseResource;
use App\Models\Api\V1\Announcement;
use App\Models\Api\V1\Client;
use App\Models\Api\V1\ValueType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class ClientService
{

    /**
     * @OA\Post(
     *     path="/api/v1/clients",
     *     summary="Register a new client",
     *     description="Creates a new client with optional personal data and document photos (front and back for CPF and RG).",
     *     operationId="storeClient",
     *     tags={"Clients"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"full_name"},
     *                 @OA\Property(property="full_name", type="string", example="João da Silva"),
     *                 @OA\Property(property="street", type="string", example="Rua das Flores, 123"),
     *                 @OA\Property(property="neighborhood", type="string", example="Centro"),
     *                 @OA\Property(property="state", type="string", maxLength=2, example="PE"),
     *                 @OA\Property(property="zip_code", type="string", example="55000-000"),
     *                 @OA\Property(property="city", type="string", example="Palmares"),
     *                 @OA\Property(property="phone", type="string", example="(81) 99999-9999"),
     *                 @OA\Property(property="rg", type="string", example="1234567"),
     *                 @OA\Property(property="cpf", type="string", example="123.456.789-00"),
     *                 @OA\Property(property="description", type="string", example="Client description"),
     *                 @OA\Property(property="legal_representative", type="string", example="Name of legal representative"),
     *                 @OA\Property(property="father_name", type="string", example="Father name"),
     *                 @OA\Property(property="mother_name", type="string", example="Mother name"),
     *
     *                 @OA\Property(
     *                     property="rg_front_photo",
     *                     type="string",
     *                     format="binary",
     *                     description="Optional RG front document photo"
     *                 ),
     *                 @OA\Property(
     *                     property="rg_back_photo",
     *                     type="string",
     *                     format="binary",
     *                     description="Optional RG back document photo"
     *                 ),
     *                 @OA\Property(
     *                     property="cpf_front_photo",
     *                     type="string",
     *                     format="binary",
     *                     description="Optional CPF front document photo"
     *                 ),
     *                 @OA\Property(
     *                     property="cpf_back_photo",
     *                     type="string",
     *                     format="binary",
     *                     description="Optional CPF back document photo"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Client registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=201),
     *             @OA\Property(property="message", type="string", example="Client registered successfully."),
     *             @OA\Property(property="data", type="object", description="The created client object"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="errors", type="object", description="Validation errors"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Failed to register client due to server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Failed to register client."),
     *             @OA\Property(property="errors", type="string", description="Exception message"),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function store(ClientStoreRequest $request): ResponseResource
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();

            if ($request->hasFile('rg_front_photo')) {
                $rgFrontPhoto = $request->file('rg_front_photo');
                if ($rgFrontPhoto->isValid()) {
                    $data['rg_front_photo_path'] = $rgFrontPhoto->store('client_documents/rg_photos', 'public');
                }
            }

            if ($request->hasFile('rg_back_photo')) {
                $rgBackPhoto = $request->file('rg_back_photo');
                if ($rgBackPhoto->isValid()) {
                    $data['rg_back_photo_path'] = $rgBackPhoto->store('client_documents/rg_photos', 'public');
                }
            }

            if ($request->hasFile('cpf_front_photo')) {
                $cpfFrontPhoto = $request->file('cpf_front_photo');
                if ($cpfFrontPhoto->isValid()) {
                    $data['cpf_front_photo_path'] = $cpfFrontPhoto->store('client_documents/cpf_photos', 'public');
                }
            }

            if ($request->hasFile('cpf_back_photo')) {
                $cpfBackPhoto = $request->file('cpf_back_photo');
                if ($cpfBackPhoto->isValid()) {
                    $data['cpf_back_photo_path'] = $cpfBackPhoto->store('client_documents/cpf_photos', 'public');
                }
            }

            $client = Client::create($data);

            DB::commit();

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_CREATED,
                'message' => 'Client registered successfully.',
                'data' => $client,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to register client.', ['exception' => $e]);

            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to register client.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }


    /**
     * @OA\Get(
     *     path="/api/v1/clients",
     *     summary="List clients",
     *     description="Returns a paginated list of clients with optional filters and sorting.",
     *     operationId="listClients",
     *     tags={"Clients"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(name="id", in="query", description="Filter by client ID", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="full_name", in="query", description="Filter by full name", @OA\Schema(type="string", example="João da Silva")),
     *     @OA\Parameter(name="street", in="query", description="Filter by street", @OA\Schema(type="string", example="Rua das Flores, 123")),
     *     @OA\Parameter(name="neighborhood", in="query", description="Filter by neighborhood", @OA\Schema(type="string", example="Centro")),
     *     @OA\Parameter(name="state", in="query", description="Filter by state", @OA\Schema(type="string", example="PE")),
     *     @OA\Parameter(name="zip_code", in="query", description="Filter by zip code", @OA\Schema(type="string", example="55000-000")),
     *     @OA\Parameter(name="city", in="query", description="Filter by city", @OA\Schema(type="string", example="Palmares")),
     *     @OA\Parameter(name="phone", in="query", description="Filter by phone", @OA\Schema(type="string", example="(81) 99999-9999")),
     *     @OA\Parameter(name="rg", in="query", description="Filter by RG", @OA\Schema(type="string", example="1234567")),
     *     @OA\Parameter(name="cpf", in="query", description="Filter by CPF", @OA\Schema(type="string", example="123.456.789-00")),
     *     @OA\Parameter(name="description", in="query", description="Filter by description", @OA\Schema(type="string", example="Special client")),
     *     @OA\Parameter(name="legal_representative", in="query", description="Filter by legal representative", @OA\Schema(type="string")),
     *     @OA\Parameter(name="father_name", in="query", description="Filter by father name", @OA\Schema(type="string")),
     *     @OA\Parameter(name="mother_name", in="query", description="Filter by mother name", @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", description="Number of items per page", @OA\Schema(type="integer", example=15)),
     *     @OA\Parameter(name="sort_by", in="query", description="Field to sort by", @OA\Schema(type="string", enum={"id", "full_name", "city", "cpf", "rg", "created_at", "updated_at"}, example="id")),
     *     @OA\Parameter(name="sort_order", in="query", description="Sort order", @OA\Schema(type="string", enum={"asc", "desc"}, example="asc")),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of clients",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example=""),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Client")),
     *                 @OA\Property(property="last_page", type="integer", example=3),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=45)
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *
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
            'id' => ['nullable', 'integer', 'exists:clients,id'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:2'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'rg' => ['nullable', 'string', 'max:30'],
            'cpf' => ['nullable', 'string', 'max:14'],
            'description' => ['nullable', 'string'],
            'legal_representative' => ['nullable', 'string'],
            'father_name' => ['nullable', 'string'],
            'mother_name' => ['nullable', 'string'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort_by' => ['nullable', 'string', Rule::in([
                'id',
                'full_name',
                'street',
                'neighborhood',
                'state',
                'zip_code',
                'city',
                'phone',
                'rg',
                'cpf',
                'description',
                'legal_representative',
                'father_name',
                'mother_name',
                'created_at',
                'updated_at'
            ])],
            'sort_order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
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

        $query = Client::query();

        foreach (
            [
                'id',
                'full_name',
                'street',
                'neighborhood',
                'state',
                'zip_code',
                'city',
                'phone',
                'rg',
                'cpf',
                'description',
                'legal_representative',
                'father_name',
                'mother_name'
            ] as $field
        ) {
            if ($request->filled($field)) {
                $query->where($field, 'like', '%' . $request->$field . '%');
            }
        }

        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 15);

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
     *     path="/api/v1/clients/{id}",
     *     summary="Update a client",
     *     description="Updates client data excluding document photos. Any combination of fields can be provided.",
     *     operationId="updateClient",
     *     tags={"Clients"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the client to update",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Client data to update",
     *         @OA\JsonContent(
     *             @OA\Property(property="full_name", type="string", example="João da Silva"),
     *             @OA\Property(property="street", type="string", example="Rua das Flores, 123"),
     *             @OA\Property(property="neighborhood", type="string", example="Centro"),
     *             @OA\Property(property="state", type="string", example="PE"),
     *             @OA\Property(property="zip_code", type="string", example="55000-000"),
     *             @OA\Property(property="city", type="string", example="Palmares"),
     *             @OA\Property(property="phone", type="string", example="(81) 99999-9999"),
     *             @OA\Property(property="rg", type="string", example="1234567"),
     *             @OA\Property(property="cpf", type="string", example="123.456.789-00"),
     *             @OA\Property(property="description", type="string", example="Updated client description"),
     *             @OA\Property(property="legal_representative", type="string", example="Name of legal representative"),
     *             @OA\Property(property="father_name", type="string", example="Father name"),
     *             @OA\Property(property="mother_name", type="string", example="Mother name")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Client updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Client updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Client"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="Validation failed."),
     *             @OA\Property(property="errors", type="object", example={"full_name": {"The full name must not exceed 255 characters."}}),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error during client update.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Failed to update client."),
     *             @OA\Property(property="errors", type="string", example="Failed to update client in database."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function update(int $id, ClientUpdateRequest $request): ResponseResource
    {
        try {
            DB::beginTransaction();
            $client = Client::findOrFail($id);
            if (!$client->update($request->validated())) {
                DB::rollBack();
                return new ResponseResource([
                    'status' => 'error',
                    'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'Failed to update client in database.',
                    'data' => null,
                    'errors' => 'Failed to update client in database.',
                ]);
            }
            DB::commit();
            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'Client updated successfully.',
                'data' => $client,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to update client.', ['exception' => $e]);
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to update client',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/v1/clients/{id}/cpf-front-photo",
     *     summary="Update CPF front photo",
     *     description="Uploads or replaces the front photo of the client's CPF document. Deletes the previous photo if it exists.",
     *     operationId="updateCpfFrontPhoto",
     *     tags={"Clients"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the client whose CPF front photo will be updated.",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="CPF front document photo upload",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"cpf_front_photo"},
     *                 @OA\Property(
     *                     property="cpf_front_photo",
     *                     type="string",
     *                     format="binary",
     *                     description="CPF front document photo (JPEG, JPG, PNG, WEBP, max 10MB)"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="CPF front photo updated successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="CPF front photo updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Client"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Invalid or unprocessable photo file.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="The provided CPF front photo is invalid."),
     *             @OA\Property(property="errors", type="string", example="The provided CPF front photo is invalid."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error during CPF front photo update.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Failed to update CPF front photo."),
     *             @OA\Property(property="errors", type="string", example="Failed to store the CPF front photo."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function updateCpfFrontPhoto(int $id, ClientUpdateCpfFrontPhotoRequest $request): ResponseResource
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();

            $client = Client::findOrFail($id);

            if ($client->cpf_front_photo_path) {
                $disk = Storage::disk('public');
                if ($disk->exists($client->cpf_front_photo_path)) {
                    $disk->delete($client->cpf_front_photo_path);
                }
            }

            $photo = $request->file('cpf_front_photo');
            if (!$photo->isValid()) {
                DB::rollBack();
                return new ResponseResource([
                    'status' => 'error',
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => 'The provided CPF front photo is invalid.',
                    'data' => null,
                    'errors' => 'The provided CPF front photo is invalid.',
                ]);
            }

            $newPath = $photo->store('client_documents/cpf_photos', 'public');

            if (!$newPath) {
                DB::rollBack();
                return new ResponseResource([
                    'status' => 'error',
                    'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'Failed to store the CPF front photo.',
                    'data' => null,
                    'errors' => 'Failed to store the CPF front photo.',
                ]);
            }


            if (!$client->update(['cpf_front_photo_path' => $newPath])) {
                DB::rollBack();
                return new ResponseResource([
                    'status' => 'error',
                    'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'Failed to update CPF front photo in database.',
                    'data' => null,
                    'errors' => 'Failed to update CPF front photo in database.',
                ]);
            }


            DB::commit();

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'CPF front photo updated successfully.',
                'data' => $client,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to update CPF front photo.', ['exception' => $e]);

            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to update CPF front photo.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }


    /**
     * @OA\Post(
     *     path="/api/v1/clients/{id}/cpf-back-photo",
     *     summary="Update CPF back photo",
     *     description="Uploads or replaces the back photo of the client's CPF document. Deletes the previous photo if it exists.",
     *     operationId="updateCpfBackPhoto",
     *     tags={"Clients"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the client whose CPF back photo will be updated.",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="CPF back document photo upload",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"cpf_back_photo"},
     *                 @OA\Property(
     *                     property="cpf_back_photo",
     *                     type="string",
     *                     format="binary",
     *                     description="CPF back document photo (JPEG, JPG, PNG, WEBP, max 10MB)"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="CPF back photo updated successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="CPF back photo updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Client"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Invalid or unprocessable photo file.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="The provided CPF back photo is invalid."),
     *             @OA\Property(property="errors", type="string", example="The provided CPF back photo is invalid."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error during CPF back photo update.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Failed to update CPF back photo."),
     *             @OA\Property(property="errors", type="string", example="Failed to store the CPF back photo."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function updateCpfBackPhoto(int $id, ClientUpdateCpfBackPhotoRequest $request): ResponseResource
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            $client = Client::findOrFail($id);

            if ($client->cpf_back_photo_path) {
                $disk = Storage::disk('public');
                if ($disk->exists($client->cpf_back_photo_path)) {
                    $disk->delete($client->cpf_back_photo_path);
                }
            }

            $photo = $request->file('cpf_back_photo');
            if (!$photo->isValid()) {
                DB::rollBack();
                return new ResponseResource([
                    'status' => 'error',
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => 'The provided CPF back photo is invalid.',
                    'data' => null,
                    'errors' => 'The provided CPF back photo is invalid.',
                ]);
            }

            $newPath = $photo->store('client_documents/cpf_photos', 'public');

            if (!$newPath) {
                DB::rollBack();
                return new ResponseResource([
                    'status' => 'error',
                    'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'Failed to store the CPF back photo.',
                    'data' => null,
                    'errors' => 'Failed to store the CPF back photo.',
                ]);
            }

            if (!$client->update(['cpf_back_photo_path' => $newPath])) {
                DB::rollBack();
                return new ResponseResource([
                    'status' => 'error',
                    'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'Failed to update CPF back photo in database.',
                    'data' => null,
                    'errors' => 'Failed to update CPF back photo in database.',
                ]);
            }

            DB::commit();

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'CPF back photo updated successfully.',
                'data' => $client,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to update CPF back photo.', ['exception' => $e]);
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to update CPF back photo.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/clients/{id}/cpf-front-photo",
     *     summary="Delete CPF front photo",
     *     description="Deletes the front photo of the client's CPF document.",
     *     operationId="deleteCpfFrontPhoto",
     *     tags={"Clients"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Client ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="CPF front photo deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="CPF front photo deleted successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Client"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function deleteCpfFrontPhoto(int $id): ResponseResource
    {
        $client = Client::findOrFail($id);

        $cpfFrontPath = $client->cpf_front_photo_path;

        if ($cpfFrontPath && Storage::disk('public')->exists($cpfFrontPath)) {
            Storage::disk('public')->delete($cpfFrontPath);
        }

        $client->update(['cpf_front_photo_path' => null]);

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'CPF front photo deleted successfully.',
            'data' => $client,
            'errors' => null,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/clients/{id}/cpf-back-photo",
     *     summary="Delete CPF back photo",
     *     description="Deletes the back photo of the client's CPF document.",
     *     operationId="deleteCpfBackPhoto",
     *     tags={"Clients"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Client ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="CPF back photo deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="CPF back photo deleted successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Client"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function deleteCpfBackPhoto(int $id): ResponseResource
    {
        $client = Client::findOrFail($id);

        $cpfBackPath = $client->cpf_back_photo_path;

        if ($cpfBackPath && Storage::disk('public')->exists($cpfBackPath)) {
            Storage::disk('public')->delete($cpfBackPath);
        }

        $client->update(['cpf_back_photo_path' => null]);

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'CPF back photo deleted successfully.',
            'data' => $client,
            'errors' => null,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/clients/{id}/rg-front-photo",
     *     summary="Update RG front photo",
     *     description="Uploads or replaces the front photo of the client's RG document. Deletes the previous photo if it exists.",
     *     operationId="updateRgFrontPhoto",
     *     tags={"Clients"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the client whose RG front photo will be updated.",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="RG front document photo upload",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"rg_front_photo"},
     *                 @OA\Property(
     *                     property="rg_front_photo",
     *                     type="string",
     *                     format="binary",
     *                     description="RG front document photo (JPEG, JPG, PNG, WEBP, max 2MB)"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="RG front photo updated successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="RG front photo updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Client"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Invalid or unprocessable photo file.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="The provided RG front photo is invalid."),
     *             @OA\Property(property="errors", type="string", example="The provided RG front photo is invalid."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error during RG front photo update.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Failed to update RG front photo."),
     *             @OA\Property(property="errors", type="string", example="Failed to store the RG front photo."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function updateRgFrontPhoto(int $id, ClientUpdateRgFrontPhotoRequest $request): ResponseResource
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();

            $client = Client::findOrFail($id);

            if ($client->rg_front_photo_path) {
                $disk = Storage::disk('public');
                if ($disk->exists($client->rg_front_photo_path)) {
                    $disk->delete($client->rg_front_photo_path);
                }
            }

            $photo = $request->file('rg_front_photo');
            if (!$photo->isValid()) {
                DB::rollBack();
                return new ResponseResource([
                    'status' => 'error',
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => 'The provided RG front photo is invalid.',
                    'data' => null,
                    'errors' => 'The provided RG front photo is invalid.',
                ]);
            }

            $newPath = $photo->store('client_documents/rg_photos', 'public');

            if (!$newPath) {
                DB::rollBack();
                return new ResponseResource([
                    'status' => 'error',
                    'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'Failed to store the RG front photo.',
                    'data' => null,
                    'errors' => 'Failed to store the RG front photo.',
                ]);
            }

            if (!$client->update(['rg_front_photo_path' => $newPath])) {
                DB::rollBack();
                return new ResponseResource([
                    'status' => 'error',
                    'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'Failed to update RG front photo in database.',
                    'data' => null,
                    'errors' => 'Failed to update RG front photo in database.',
                ]);
            }

            DB::commit();

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'RG front photo updated successfully.',
                'data' => $client,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to update RG front photo.', ['exception' => $e]);

            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to update RG front photo.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/clients/{id}/rg-back-photo",
     *     summary="Update RG back photo",
     *     description="Uploads or replaces the back photo of the client's RG document. Deletes the previous photo if it exists.",
     *     operationId="updateRgBackPhoto",
     *     tags={"Clients"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the client whose RG back photo will be updated.",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="RG back document photo upload",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"rg_back_photo"},
     *                 @OA\Property(
     *                     property="rg_back_photo",
     *                     type="string",
     *                     format="binary",
     *                     description="RG back document photo (JPEG, JPG, PNG, WEBP, max 10MB)"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="RG back photo updated successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="RG back photo updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Client"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Invalid or unprocessable RG back photo.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="The provided RG back photo is invalid."),
     *             @OA\Property(property="errors", type="string", example="The provided RG back photo is invalid."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error during RG back photo update.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Failed to update RG back photo."),
     *             @OA\Property(property="errors", type="string", example="Failed to store the RG back photo."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function updateRgBackPhoto(int $id, ClientUpdateRgBackPhotoRequest $request): ResponseResource
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();

            $client = Client::findOrFail($id);

            if ($client->rg_back_photo_path) {
                $disk = Storage::disk('public');
                if ($disk->exists($client->rg_back_photo_path)) {
                    $disk->delete($client->rg_back_photo_path);
                }
            }

            $photo = $request->file('rg_back_photo');
            if (!$photo->isValid()) {
                DB::rollBack();
                return new ResponseResource([
                    'status' => 'error',
                    'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'message' => 'The provided RG back photo is invalid.',
                    'data' => null,
                    'errors' => 'The provided RG back photo is invalid.',
                ]);
            }

            $newPath = $photo->store('client_documents/rg_photos', 'public');

            if (!$newPath) {
                DB::rollBack();
                return new ResponseResource([
                    'status' => 'error',
                    'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'Failed to store the RG back photo.',
                    'data' => null,
                    'errors' => 'Failed to store the RG back photo.',
                ]);
            }

            if (!$client->update(['rg_back_photo_path' => $newPath])) {
                DB::rollBack();
                return new ResponseResource([
                    'status' => 'error',
                    'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'Failed to update RG back photo in database.',
                    'data' => null,
                    'errors' => 'Failed to update RG back photo in database.',
                ]);
            }

            DB::commit();

            return new ResponseResource([
                'status' => 'success',
                'status_code' => Response::HTTP_OK,
                'message' => 'RG back photo updated successfully.',
                'data' => $client,
                'errors' => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to update RG back photo.', ['exception' => $e]);
            return new ResponseResource([
                'status' => 'error',
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Failed to update RG back photo.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/clients/{id}/rg-front-photo",
     *     summary="Delete RG front photo",
     *     description="Deletes the front photo of the client's RG document.",
     *     operationId="deleteRgFrontPhoto",
     *     tags={"Clients"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Client ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="RG front photo deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="RG front photo deleted successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Client"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function deleteRgFrontPhoto(int $id): ResponseResource
    {
        $client = Client::findOrFail($id);

        $rgFrontPath = $client->rg_front_photo_path;

        if ($rgFrontPath && Storage::disk('public')->exists($rgFrontPath)) {
            Storage::disk('public')->delete($rgFrontPath);
        }

        $client->update(['rg_front_photo_path' => null]);

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'RG front photo deleted successfully.',
            'data' => $client,
            'errors' => null,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/clients/{id}/rg-back-photo",
     *     summary="Delete RG back photo",
     *     description="Deletes the back photo of the client's RG document.",
     *     operationId="deleteRgBackPhoto",
     *     tags={"Clients"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Client ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="RG back photo deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="RG back photo deleted successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Client"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function deleteRgBackPhoto(int $id): ResponseResource
    {
        $client = Client::findOrFail($id);

        $rgBackPath = $client->rg_back_photo_path;

        if ($rgBackPath && Storage::disk('public')->exists($rgBackPath)) {
            Storage::disk('public')->delete($rgBackPath);
        }

        $client->update(['rg_back_photo_path' => null]);

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'RG back photo deleted successfully.',
            'data' => $client,
            'errors' => null,
        ]);
    }


    /**
     * @OA\Delete(
     *     path="/api/v1/clients/{id}",
     *     summary="Delete client",
     *     description="Deletes the client and removes associated CPF and RG front/back photos.",
     *     operationId="deleteClient",
     *     tags={"Clients"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Client ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Client deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Client deleted successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1)
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Client not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=404),
     *             @OA\Property(property="message", type="string", example="Client not found."),
     *             @OA\Property(property="errors", type="string", example="Resource not found."),
     *             @OA\Property(property="data", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function remove(int $id): ResponseResource
    {
        $client = Client::findOrFail($id);

        $photoFields = [
            'cpf_front_photo_path',
            'cpf_back_photo_path',
            'rg_front_photo_path',
            'rg_back_photo_path'
        ];

        foreach ($photoFields as $field) {
            if ($client->$field && Storage::disk('public')->exists($client->$field)) {
                Storage::disk('public')->delete($client->$field);
            }
        }

        $client->delete();

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'Client deleted successfully.',
            'data' => ['id' => $id],
            'errors' => null,
        ]);
    }
}
