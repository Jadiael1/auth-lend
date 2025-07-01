<?php

namespace App\Models\Api\V1;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     description="User model of the system",
 *     @OA\Xml(name="User"),
 *
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Unique identifier for the user",
 *         example=1,
 *         readOnly=true
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="First name of the user",
 *         example="John"
 *     ),
 *     @OA\Property(
 *         property="surname",
 *         type="string",
 *         description="Surname of the user",
 *         example="Doe"
 *     ),
 *     @OA\Property(
 *         property="user",
 *         type="string",
 *         description="User of the user",
 *         example="JohnDoe"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="User's email address (unique)",
 *         example="john.doe@example.com"
 *     ),
 *     @OA\Property(
 *         property="is_admin",
 *         type="boolean",
 *         description="Indicates whether the user is an administrator",
 *         example=false
 *     ),
 *     @OA\Property(
 *         property="permissions",
 *         type="array",
 *         description="List of permissions assigned to the user",
 *         @OA\Items(type="string"),
 *         example={"*"},
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="phone",
 *         type="string",
 *         nullable=true,
 *         description="User's phone number",
 *         example="5581999998888"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="boolean",
 *         description="Whether the user's account is active (true) or inactive (false)",
 *         example=true
 *     ),
 *     @OA\Property(
 *         property="email_verified_at",
 *         type="string",
 *         format="date-time",
 *         description="Datetime when the user's email was verified (UTC)",
 *         example="2025-06-04T20:30:00Z",
 *         nullable=true,
 *         readOnly=true
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Datetime when the user record was created (UTC)",
 *         example="2025-06-04T20:30:00Z",
 *         readOnly=true
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Datetime when the user record was last updated (UTC)",
 *         example="2025-06-04T20:30:00Z",
 *         readOnly=true
 *     )
 * )
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'surname',
        'user',
        'email',
        'password',
        'is_admin',
        'permissions',
        'phone',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime:UTC',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'permissions' => 'array',
            'status' => 'boolean',
            'created_at' => 'datetime:UTC',
            'updated_at' => 'datetime:UTC'
        ];
    }
}
