<?php

namespace App\Models\Api\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;



/**
 * @OA\Schema(
 * schema="StoreAddress",
 * title="Store Address",
 * description="Store Address Template",
 * @OA\Xml(name="StoreAddress"),
 * @OA\Property(property="id", type="integer", format="int64", description="Unique Address ID", readOnly=true, example=1),
 * @OA\Property(property="uf", type="string", maxLength=2, description="Federative Unit (State)", example="PE"),
 * @OA\Property(property="city", type="string", maxLength=255, description="City", example="Recife"),
 * @OA\Property(property="neighborhood", type="string", maxLength=255, description="Neighborhood", example="Boa Viagem"),
 * @OA\Property(property="zip_code", type="string", maxLength=10, description="CEP (Postal Address Code)", example="51020-000"),
 * @OA\Property(property="street", type="string", maxLength=255, description="Street/street name", example="Avenida Boa Viagem"),
 * @OA\Property(property="number", type="integer", description="Establishment number", example=1234),
 * @OA\Property(property="coordinates", type="string", description="Geographic coordinates in 'latitude, longitude' format", example="-8.127690,-34.899860"),
 * @OA\Property(property="store_id", type="integer", format="int64", description="Associated store ID (usually not returned when nested)", readOnly=true, nullable=true),
 * @OA\Property(property="created_at", type="string", format="date-time", description="Creation date and time (UTC)", readOnly=true, example="2025-06-05T03:00:00Z"),
 * @OA\Property(property="updated_at", type="string", format="date-time", description="Last updated date and time (UTC)", readOnly=true, example="2025-06-05T03:00:00Z")
 * )
 */

class StoreAddress extends Model
{
    //
    protected $fillable = [
        'uf',
        'city',
        'neighborhood',
        'zip_code',
        'street',
        'number',
        'coordinates',
        'store_id'
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime:UTC',
            'updated_at' => 'datetime:UTC'
        ];
    }
}
