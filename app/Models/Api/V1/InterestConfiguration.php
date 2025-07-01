<?php

namespace App\Models\Api\V1;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="InterestConfiguration",
 *     type="object",
 *     title="Interest Configuration",
 *     required={"card_flag_id", "store_id", "installments", "interest_rate", "value_type_id"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="card_flag_id", type="integer", example=1),
 *     @OA\Property(property="store_id", type="integer", example=2),
 *     @OA\Property(property="installments", type="integer", example=12),
 *     @OA\Property(property="interest_rate", type="number", format="float", example=2.50),
 *     @OA\Property(property="value_type_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-08T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-08T12:00:00Z")
 * )
 */
class InterestConfiguration extends Model
{
    //
    protected $fillable = [
        'card_flag_id',
        'store_id',
        'value_type_id',
        'installments',
        'interest_rate',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime:UTC',
            'updated_at' => 'datetime:UTC',
            'interest_rate' => 'decimal:2',
        ];
    }

    public function cardFlag()
    {
        return $this->belongsTo(CardFlag::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function valueType()
    {
        return $this->belongsTo(ValueType::class);
    }
}
