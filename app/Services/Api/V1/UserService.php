<?php

namespace App\Services\Api\V1;

use App\Http\Requests\Api\V1\SignUpRequest;
use App\Http\Resources\Api\V1\ResponseResource;
use App\Models\Api\V1\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class UserService
{

    /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     summary="List users with optional filters, sorting and field selection - ADM",
     *     description="Returns a paginated list of users with optional filters, field inclusion/exclusion, and sorting.",
     *     operationId="getUsers",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Filter by user ID (partial match)",
     *         required=false,
     *         example="1",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Filter by user name (partial match)",
     *         required=false,
     *         example="John",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="surname",
     *         in="query",
     *         description="Filter by user surname (partial match)",
     *         required=false,
     *         example="Doe",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="user",
     *         in="query",
     *         description="Filter by user (partial match)",
     *         required=false,
     *         example="Doe",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         description="Filter by user email (partial match)",
     *         required=false,
     *         example="john@example.com",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="email_verified_at",
     *         in="query",
     *         description="Filter by email verification timestamp (partial match)",
     *         required=false,
     *         example="2025-06-04",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="is_admin",
     *         in="query",
     *         description="Filter by admin status (partial match)",
     *         required=false,
     *         example="1",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         description="Filter by phone number (partial match)",
     *         required=false,
     *         example="5581999998888",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by active/inactive status (partial match)",
     *         required=false,
     *         example="1",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="created_at",
     *         in="query",
     *         description="Filter by creation timestamp (partial match)",
     *         required=false,
     *         example="2025-06-01",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="updated_at",
     *         in="query",
     *         description="Filter by update timestamp (partial match)",
     *         required=false,
     *         example="2025-06-04",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Column to sort by (default: created_at)",
     *         required=false,
     *         example="name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Sort order: asc or desc (default: desc)",
     *         required=false,
     *         example="asc",
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of users per page (pagination)",
     *         required=false,
     *         example="15",
     *         @OA\Schema(type="integer", format="int32")
     *     ),
     *     @OA\Parameter(
     *         name="only_fields",
     *         in="query",
     *         description="Comma-separated list of fields to include",
     *         required=false,
     *         example="id,name,email"
     *     ),
     *     @OA\Parameter(
     *         name="except_fields",
     *         in="query",
     *         description="Comma-separated list of fields to exclude",
     *         required=false,
     *         example="phone,updated_at"
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of users returned successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Users listed successfully."),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=74)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(Request $request): ResponseResource
    {
        $query = User::query();

        $fields = [
            'id',
            'name',
            'surname',
            'user',
            'email',
            'email_verified_at',
            'is_admin',
            'phone',
            'status',
            'created_at',
            'updated_at'
        ];

        foreach ($request->only($fields) as $field => $value) {
            if (!is_null($value)) {
                $query->where($field, 'like', "%{$value}%");
            }
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $perPage = $request->get('per_page', 15);
        $paginator = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);
        $onlyFields = array_filter(explode(',', $request->get('only_fields', '')));
        $exceptFields = array_filter(explode(',', $request->get('except_fields', '')));

        $paginator->getCollection()->transform(function ($user) use ($onlyFields, $exceptFields) {
            $data = collect($user);
            if (!empty($onlyFields)) {
                $data = $data->only($onlyFields);
            }
            if (!empty($exceptFields)) {
                $data = $data->except($exceptFields);
            }
            return $data;
        });

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'Users listed successfully.',
            'data' => $paginator,
            'errors' => null,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/auth/user",
     *     operationId="show",
     *     tags={"Auth"},
     *     summary="Get authenticated user",
     *     description="Returns the authenticated user's data",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Success: Resource successfully recovered",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", format="int64", example="200"),
     *             @OA\Property(property="message", type="string", example="User successfully recovered."),
     *             @OA\Property(property="data", ref="#/components/schemas/User"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Error: Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", format="int64", example="401"),
     *             @OA\Property(property="message", type="string", example="Unauthenticated."),
     *             @OA\Property(property="data", type="object", nullable=true, example=null),
     *             @OA\Property(property="errors", type="object", nullable=true, example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error: Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="exception", type="string"),
     *             @OA\Property(property="file", type="string"),
     *             @OA\Property(property="line", type="integer", format="int64"),
     *             @OA\Property(property="trace", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function show(Request $request): ResponseResource
    {
        $user = $request->user();
        return (new ResponseResource([
            'status' => 'success',
            'status_code' => 200,
            'message' => 'User returned successfully',
            'data' => $user,
            'errors' => null
        ]));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/signup",
     *     summary="Register a new user",
     *     description="Creates a new user. The first registered user will automatically be set as an administrator.",
     *     operationId="storeUser",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="User registration payload",
     *         @OA\JsonContent(
     *             required={"name", "surname", "user", "email", "password", "password_confirmation", "phone"},
     *             @OA\Property(property="name", type="string", example="John"),
     *             @OA\Property(property="surname", type="string", example="Doe"),
     *             @OA\Property(property="user", type="string", example="JohnDoe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password_confirmation", example="secret123"),
     *             @OA\Property(property="phone", type="string", example="5581999998888"),
     *             @OA\Property(property="permissions",type="array",description="List of permissions assigned to the user",@OA\Items(type="string"),example={"api/v1/clients", "api/v1/simulations", "api/v1/stores"})
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="User successfully registered",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=201),
     *             @OA\Property(property="message", type="string", example="User registered successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/User"),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Failed to register user."),
     *             @OA\Property(property="errors", type="string", example="Exception message here"),
     *             @OA\Property(property="data", type="string", nullable=true, example=null)
     *         )
     *     )
     * )
     */
    public function store(array $request): ResponseResource
    {
        try {
            DB::beginTransaction();
            $userCount = User::count();
            $isAdmin = $userCount === 0;

            $request['permissions'] = null;
            $request['status'] = false;

            $user = User::create([
                ...$request,
                'password'    => Hash::make($request['password']),
                'is_admin'    => $isAdmin,
            ]);
            $user->markEmailAsVerified();
            DB::commit();
            return new ResponseResource([
                'status'      => 'success',
                'status_code' => Response::HTTP_CREATED,
                'message'     => 'User registered successfully.',
                'data'        => $user,
                'errors'      => null,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return new ResponseResource([
                'status' => 'error',
                'status_code' => 500,
                'message' => 'Failed to register user.',
                'data' => null,
                'errors' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/users/{user}/permissions",
     *     summary="Update user permissions",
     *     description="Updates the permissions of a specific user. Requires admin user with '*' ability.",
     *     operationId="updatePermissions",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID of the user to update permissions",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="List of permissions to assign to the user",
     *         @OA\JsonContent(
     *             required={"permissions"},
     *             @OA\Property(
     *                 property="permissions",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"api/v1/clients", "api/v1/stores", "api/v1/simulations"}
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Permissions updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Permissions updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/User"),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function updatePermissions(Request $request, User $user): ResponseResource
    {
        $validated = $request->validate([
            'permissions' => ['nullable', 'array', 'min:1'],
            'permissions.*' => ['string']
        ]);

        $user->permissions = $validated['permissions'];
        $user->save();

        $user->tokens()->delete();

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'Permissions updated successfully.',
            'data' => $user,
            'errors' => null,
        ]);
    }


    /**
     * @OA\Put(
     *     path="/api/v1/users/{user}/admin",
     *     summary="Update user admin status",
     *     description="Toggles the admin status of a specific user. Requires admin user with '*' ability.",
     *     operationId="updateIsAdmin",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID of the user to update admin status",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Admin status to assign to the user",
     *         @OA\JsonContent(
     *             required={"is_admin"},
     *             @OA\Property(
     *                 property="is_admin",
     *                 type="boolean",
     *                 example=true
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User admin status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="User admin status updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/User"),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function updateIsAdmin(Request $request, User $user): ResponseResource
    {
        $validated = $request->validate([
            'is_admin' => ['required', 'boolean']
        ]);

        $user->is_admin = $validated['is_admin'];
        $user->save();

        if (!$validated['is_admin']) {
            $user->tokens()->delete();
        }

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'User admin status updated successfully.',
            'data' => $user,
            'errors' => null
        ]);
    }


    /**
     * @OA\Put(
     *     path="/api/v1/users/{user}/status",
     *     summary="Update user status",
     *     description="Activates or deactivates a specific user. Requires admin user with '*' ability.",
     *     operationId="updateStatus",
     *     tags={"Users"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="user",
     *         in="path",
     *         description="ID of the user to update status",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Status flag to assign to the user",
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="User status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="User status updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/User"),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=422),
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function updateStatus(Request $request, User $user): ResponseResource
    {
        $validated = $request->validate([
            'status' => ['required', 'boolean']
        ]);

        $user->status = $validated['status'];
        $user->save();

        if (!$validated['status']) {
            $user->tokens()->delete();
        }

        return new ResponseResource([
            'status' => 'success',
            'status_code' => Response::HTTP_OK,
            'message' => 'User status updated successfully.',
            'data' => $user,
            'errors' => null
        ]);
    }
}
