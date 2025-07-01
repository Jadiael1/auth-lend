<?php

namespace App\Models\Api\V1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

 /**
 * @OA\Schema(
 * schema="PhotoStore",
 * title="Photo Store",
 * description="Store Photo Template",
 * @OA\Xml(name="PhotoStore"),
 * @OA\Property(property="id", type="integer", format="int64", description="Unique Photo ID", readOnly=true, example=1),
 * @OA\Property(property="photo_path", type="string", format="url", description="Full store photo URL", example="store_photos/UfWw2MJ7UPg87mIcc1wpdWVXLaeZas1ABLS1Fl11.jpg"),
 * @OA\Property(property="store_id", type="integer", format="int64", description="Associated store ID", readOnly=true, nullable=true),
 * @OA\Property(property="created_at", type="string", format="date-time", description="Creation date and time (UTC)", readOnly=true, example="2025-06-05T03:00:00Z"),
 * @OA\Property(property="updated_at", type="string", format="date-time", description="Last updated date and time (UTC)", readOnly=true, example="2025-06-05T03:00:00Z")
 * )
 */

class PhotoStore extends Model
{
    //

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'photo_path',
        'store_id',
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
