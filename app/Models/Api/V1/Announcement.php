<?php

namespace App\Models\Api\V1;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Announcement",
 *     title="Announcement",
 *     description="Announcement model",
 *     @OA\Xml(name="Announcement"),
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Promoção especial"),
 *     @OA\Property(property="subtitle", type="string", example="Somente esta semana!"),
 *     @OA\Property(property="message", type="string", example="Aproveite descontos exclusivos em nossos serviços."),
 *     @OA\Property(property="button_text", type="string", example="Saiba mais"),
 *     @OA\Property(property="button_url", type="string", example="https://exemplo.com/acao"),
 *     @OA\Property(property="note", type="string", example="Válido até sexta-feira."),
 *     @OA\Property(property="order", type="integer", example=1),
 *     @OA\Property(property="status", type="boolean", example=true),
 *     @OA\Property(property="photo_path", type="string", example="announcement_photos/banner1.png"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-09T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-09T12:00:00Z")
 * )
 */
class Announcement extends Model
{
    //
    protected $fillable = [
        'title',
        'subtitle',
        'message',
        'button_text',
        'button_url',
        'note',
        'order',
        'status',
        'photo_path',
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
