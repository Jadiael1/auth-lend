<?php

namespace App\Models\Api\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="Simulation",
 *     type="object",
 *     title="Simulation",
 *     required={"amount", "amount_with_interest", "interest_rate_by_type_of_amount", "interest_rate_by_number_of_installments", "installments", "value_type_id", "store_id", "card_flag_id"},
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="amount", type="number", format="float", example=1000.00),
 *     @OA\Property(property="amount_with_interest", type="number", format="float", example=1125.50),
 *     @OA\Property(property="interest_rate_by_type_of_amount", type="number", format="float", example=1.50),
 *     @OA\Property(property="interest_rate_by_number_of_installments", type="number", format="float", example=0.75),
 *     @OA\Property(property="installments", type="integer", example=12),
 *     @OA\Property(property="installment_value", type="number", format="float", example=55.75),
 *     @OA\Property(property="value_type_id", type="integer", example=2),
 *     @OA\Property(property="store_id", type="integer", example=5),
 *     @OA\Property(property="card_flag_id", type="integer", example=3),
 *     @OA\Property(property="ip", type="string", format="ipv4", example="192.168.0.1"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-11T18:32:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-11T18:32:00Z")
 * )
 */
class Simulation extends Model
{
    //
    protected $fillable = [
        'uuid',
        'amount',
        'amount_with_interest',
        'interest_rate_by_type_of_amount',
        'interest_rate_by_number_of_installments',
        'value_type_id',
        'installments',
        'installment_value',
        'store_id',
        'card_flag_id',
        'ip',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function cardFlag(): BelongsTo
    {
        return $this->belongsTo(CardFlag::class);
    }

    public function valueType(): BelongsTo
    {
        return $this->belongsTo(ValueType::class);
    }

    protected function casts(): array
    {
        return [
            'amount'                                      => 'decimal:2',
            'amount_with_interest'                        => 'decimal:2',
            'interest_rate_by_type_of_amount'             => 'decimal:2',
            'interest_rate_by_number_of_installments'     => 'decimal:2',
            'installment_value'                           => 'decimal:2',
            'created_at'                                  => 'datetime:UTC',
            'updated_at'                                  => 'datetime:UTC'
        ];
    }


}
