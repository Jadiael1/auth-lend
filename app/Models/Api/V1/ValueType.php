<?php

namespace App\Models\Api\V1;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *  schema="ValueType",
 *  title="Value Type",
 *  description="Value Type Model",
 *  @OA\Xml(name="ValueType"),
 *  @OA\Property(property="id", type="integer", format="int64", description="Unique Value Type ID", readOnly=true, example=1),
 *  @OA\Property(property="type", type="string", maxLength=255, description="Value Type", example="Valor Desejado / Limite Total"),
 *  @OA\Property(property="created_at", type="string", format="date-time", description="Creation date and time (UTC)", readOnly=true, example="2025-06-05T03:00:00Z"),
 *  @OA\Property(property="updated_at", type="string", format="date-time", description="Last updated date and time (UTC)", readOnly=true, example="2025-06-05T03:00:00Z")
 * )
 */
class ValueType extends Model
{
    //
    protected $fillable = [
        'type',
        'interest_rate',
        'direction'
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime:UTC',
            'updated_at' => 'datetime:UTC',
            'interest_rate' => 'decimal:2',
        ];
    }
}
