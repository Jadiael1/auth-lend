<?php

namespace App\Services\Api\V1;

use App\Http\Controllers\Api\V1\UserController;
use App\Http\Resources\Api\V1\ResponseResource;
use App\Models\Api\V1\User;
use App\Notifications\Api\V1\CustomResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthService
{

    /**
     * @OA\Post(
     *     path="/api/v1/auth/signin",
     *     summary="Authenticate user and return access token",
     *     description="Logs in a user using email, username, or phone. Returns a Bearer token upon success.",
     *     operationId="signin",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="User credentials",
     *         @OA\JsonContent(
     *             required={"credential", "password"},
     *             @OA\Property(
     *                 property="credential",
     *                 type="string",
     *                 description="Email, username or phone used to authenticate",
     *                 example="john@example.com"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 description="User's password",
     *                 example="secret123"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Authentication successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Login successful."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJK..."),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="string", format="date-time", description="Datetime when the token will expire", example="2025-06-04 20:30:00")
     *             ),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Invalid credentials."),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=403,
     *         description="Account inactive or email not verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=403),
     *             @OA\Property(property="message", type="string", example="Your account is inactive or your email has not been verified. Please activate your account or contact support."),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     )
     * )
     */
    public function signin(Request $request)
    {
        $credentials = $request->validate([
            'credential' => ['required'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials["credential"])
            ->orWhere('user', $credentials["credential"])
            ->orWhere('phone', $credentials["credential"])
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return (new ResponseResource([
                'status' => 'error',
                'status_code' => 401,
                'message' => 'Invalid credentials.',
                'data' => null,
                'errors' => null
            ]));
        }

        if (!$user->status) {
            return (new ResponseResource([
                'status' => 'error',
                'status_code' => 403,
                'message' => 'Your account is inactive. Please contact support.',
                'data' => null,
                'errors' => null
            ]));
        }

        if (!$user->hasVerifiedEmail()) {
            return (new ResponseResource([
                'status' => 'error',
                'status_code' => 403,
                'message' => 'Your account is not verified yet, please verify your account before logging in.',
                'data' => null,
                'errors' => null,
            ]));
        }

        $tokenResult = $user->createToken('auth_token', $user->permissions ?? []);
        $token = $tokenResult->plainTextToken;

        $expirationMinutes = (int) env('SIGNIN_PASSWORD_EXPIRE', 60);
        $expiration = Carbon::now()->addMinutes($expirationMinutes);

        $tokenResult->accessToken->expires_at = $expiration;
        $tokenResult->accessToken->save();

        $expirationInTimezone = $expiration->toDateTimeString();

        return (new ResponseResource([
            'status' => 'success',
            'status_code' => 200,
            'message' => 'Login successful.',
            'data' => ['user' => $user, 'token' => $token, 'token_type' => 'Bearer', 'expires_in' => $expirationInTimezone],
            'errors' => null,
        ]));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/signout",
     *     summary="Logout the authenticated user",
     *     description="Revokes the current access token for the authenticated user.",
     *     operationId="signout",
     *     tags={"Auth"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="status_code", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="Logout successful."),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized or token invalid",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="status_code", type="integer", example=401),
     *             @OA\Property(property="message", type="string", example="Token has expired or is invalid."),
     *             @OA\Property(property="data", type="string", nullable=true, example=null),
     *             @OA\Property(property="errors", type="string", nullable=true, example=null)
     *         )
     *     )
     * )
     */
    public function signout(Request $request)
    {
        $user = $request->user();
        $token = $user ? $user->currentAccessToken() : null;

        if ($token) {
            $token->delete();
            return (new ResponseResource([
                'status' => 'success',
                'status_code' => 200,
                'message' => 'Logout successful.',
                'data' => null,
                'errors' => null,
            ]));
        }

        return (new ResponseResource([
            'status' => 'error',
            'status_code' => 401,
            'message' => 'Token has expired or is invalid.',
            'data' => null,
            'errors' => null,
        ]));
    }
}
