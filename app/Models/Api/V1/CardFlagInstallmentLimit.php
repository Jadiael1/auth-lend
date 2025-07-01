<?php

namespace App\Models\Api\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="CardFlagInstallmentLimit",
 *     title="Card Flag Installment Limit",
 *     description="Defines minimum value allowed per number of installments for a card flag",
 *     @OA\Xml(name="CardFlagInstallmentLimit"),
 *     @OA\Property(property="id", type="integer", format="int64", readOnly=true, example=1),
 *     @OA\Property(property="card_flag_id", type="integer", example=1, description="ID of related card flag"),
 *     @OA\Property(property="installments", type="integer", example=12, description="Number of allowed installments"),
 *     @OA\Property(property="min_value", type="number", format="decimal", example=100.00, description="Minimum value for each installment"),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true)
 * )
 */
class CardFlagInstallmentLimit extends Model
{
    //
    protected $table = 'card_flag_installment_limits';

    protected $fillable = ['card_flag_id', 'installments', 'min_value'];

    public function cardFlag(): BelongsTo
    {
        return $this->belongsTo(CardFlag::class);
    }

    protected function casts(): array
    {
        return [
            'min_value' => 'decimal:2',
            'created_at' => 'datetime:UTC',
            'updated_at' => 'datetime:UTC',
        ];
    }

}
