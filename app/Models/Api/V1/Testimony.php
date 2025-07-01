<?php

namespace App\Models\Api\V1;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Testimony",
 *     title="Testimony",
 *     description="Testimony Model",
 *     @OA\Xml(name="Testimony"),
 *
 *     @OA\Property(property="id", type="integer", format="int64", readOnly=true, example=1, description="Unique identifier for the testimony"),
 *     @OA\Property(property="name", type="string", maxLength=255, example="Maria", description="First name of the person giving the testimony"),
 *     @OA\Property(property="surname", type="string", maxLength=255, example="Silva", description="Last name of the person giving the testimony"),
 *     @OA\Property(property="city", type="string", maxLength=255, example="Recife", description="City of residence"),
 *     @OA\Property(property="uf", type="string", maxLength=2, example="PE", description="State abbreviation"),
 *     @OA\Property(property="photo_path", type="string", nullable=true, example="testimony_photos/photo123.png", description="Path to the testimony photo"),
 *     @OA\Property(property="message", type="string", example="The service was excellent and helped me a lot!", description="Message or content of the testimony"),
 *     @OA\Property(property="status", type="boolean", description="If the testimony is approved (true) or pending and disapproved (false)",example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly=true, example="2025-06-09T10:00:00Z", description="Creation timestamp (UTC)"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true, example="2025-06-09T12:30:00Z", description="Last update timestamp (UTC)")
 * )
 */


class Testimony extends Model
{
    //
    protected $fillable = [
        'name',
        'surname',
        'city',
        'uf',
        'photo_path',
        'message',
        'status'
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'created_at' => 'datetime:UTC',
            'updated_at' => 'datetime:UTC',
        ];
    }
}
