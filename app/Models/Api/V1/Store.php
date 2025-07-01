<?php

namespace App\Models\Api\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @OA\Schema(
 *  schema="Store",
 *  title="Store",
 *  description="Store Model",
 *  @OA\Xml(name="Store"),
 *  @OA\Property(property="id", type="integer", format="int64", description="Unique Store ID", readOnly=true, example=1),
 *  @OA\Property(property="name", type="string", maxLength=255, description="Store name", example="Cred Mais - Unidade Centro"),
 *  @OA\Property(property="address", type="object", description="address associated with the store", ref="#/components/schemas/StoreAddress", nullable=true),
 *  @OA\Property(property="photo_stores", type="array", description="List of photos associated with the store", @OA\Items(ref="#/components/schemas/PhotoStore"), nullable=true),
 *  @OA\Property(property="created_at", type="string", format="date-time", description="Creation date and time (UTC)", readOnly=true, example="2025-06-05T03:00:00Z"),
 *  @OA\Property(property="updated_at", type="string", format="date-time", description="Last updated date and time (UTC)", readOnly=true, example="2025-06-05T03:00:00Z")
 * )
 */
class Store extends Model
{
    //
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];

    public function address(): HasOne
    {
        return $this->hasOne(StoreAddress::class);
    }

    public function photoStores(): HasMany
    {
        return $this->hasMany(PhotoStore::class);
    }

    public function interestConfigurations(): HasMany
    {
        return $this->hasMany(InterestConfiguration::class);
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
