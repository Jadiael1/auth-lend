<?php

namespace App\Models\Api\V1;

use Illuminate\Database\Eloquent\Model;


/**
 * @OA\Schema(
 *     schema="Solution",
 *     title="Solution",
 *     description="A solution entity representing a single service or offering with an image",
 *     @OA\Xml(name="Solution"),
 *
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="Unique identifier for the solution",
 *         example=1,
 *         readOnly=true
 *     ),
 *
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         maxLength=255,
 *         description="Title of the solution",
 *         example="Quick Loan for Retirees"
 *     ),
 *
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         maxLength=255,
 *         description="Short description of the solution",
 *         example="Exclusive credit line with low interest rates for retirees and pensioners."
 *     ),
 *
 *     @OA\Property(
 *         property="photo_path",
 *         type="string",
 *         format="url",
 *         description="Public path to the image associated with the solution",
 *         example="solutions_photos/loan_benefit_webp.jpg"
 *     ),

 *     @OA\Property(
 *         property="width",
 *         type="string",
 *         description="Width of the solution image (in pixels or percent)",
 *         example="100%"
 *     ),

 *     @OA\Property(property="order", type="integer", description="Defines the order in which the solution will be displayed", example=1),

 *     @OA\Property(
 *         property="height",
 *         type="string",
 *         description="Height of the solution image (in pixels or percent)",
 *         example="400px"
 *     ),

 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp of when the solution was created (UTC)",
 *         readOnly=true,
 *         example="2025-06-07T01:00:00Z"
 *     ),

 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp of the last update to the solution (UTC)",
 *         readOnly=true,
 *         example="2025-06-07T01:30:00Z"
 *     )
 * )
 */
class Solution extends Model
{
    //

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'photo_path',
        'order',
        'width',
        'height',
    ];


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
