<?php

namespace App\Models\Api\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @OA\Schema(
 *     schema="CardFlag",
 *     title="Card Flag",
 *     description="Represents a credit card flag (brand) like Mastercard, Visa, etc.",
 *     @OA\Xml(name="CardFlag"),
 *
 *     @OA\Property(property="id", type="integer", format="int64", readOnly=true, example=1, description="Unique Card Flag ID"),
 *     @OA\Property(property="name", type="string", maxLength=255, example="MasterCard", description="Name of the card flag"),
 *     @OA\Property(property="photo_path", type="string", nullable=true, example="card_flags/mastercard.svg", description="Storage path to the card flag image (PNG or SVG)"),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly=true, example="2025-06-07T03:00:00Z", description="Creation timestamp (UTC)"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true, example="2025-06-07T03:00:00Z", description="Last updated timestamp (UTC)")
 * )
 */
class CardFlag extends Model
{
    //
    protected $fillable = [
        'name',
        'photo_path',
    ];

    public function cardFlagInstallmentLimit(): HasOne
    {
        return $this->hasOne(CardFlagInstallmentLimit::class);
    }

    public function interestConfigurations(): HasMany
    {
        return $this->hasMany(InterestConfiguration::class);
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime:UTC',
            'updated_at' => 'datetime:UTC',
        ];
    }
}
