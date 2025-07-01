<?php

namespace App\Models\Api\V1;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Client",
 *     title="Client",
 *     description="Client model",
 *     @OA\Xml(name="Client"),
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="full_name", type="string", example="João da Silva"),
 *     @OA\Property(property="street", type="string", example="1ª Travessa do Meio, 123", nullable=true),
 *     @OA\Property(property="neighborhood", type="string", example="Boa Viagem", nullable=true),
 *     @OA\Property(property="state", type="string", example="PE", nullable=true),
 *     @OA\Property(property="zip_code", type="string", example="51150-601", nullable=true),
 *     @OA\Property(property="city", type="string", example="Recife", nullable=true),
 *     @OA\Property(property="phone", type="string", example="(81) 99999-9999", nullable=true),
 *     @OA\Property(property="rg", type="string", example="1234567", nullable=true),
 *     @OA\Property(property="rg_front_photo_path", type="string", example="client_documents/rg_front_joao.png", nullable=true),
 *     @OA\Property(property="rg_back_photo_path", type="string", example="client_documents/rg_back_joao.png", nullable=true),
 *     @OA\Property(property="cpf", type="string", example="123.456.789-00", nullable=true),
 *     @OA\Property(property="cpf_front_photo_path", type="string", example="client_documents/cpf_front_joao.png", nullable=true),
 *     @OA\Property(property="cpf_back_photo_path", type="string", example="client_documents/cpf_back_joao.png", nullable=true),
 *     @OA\Property(property="description", type="string", example="Client with complete documentation", nullable=true),
 *     @OA\Property(property="legal_representative", type="string", example="Name of legal representative", nullable=true),
 *     @OA\Property(property="father_name", type="string", example="father name", nullable=true),
 *     @OA\Property(property="mother_name", type="string", example="mother name", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-09T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-09T10:30:00Z")
 * )
 */
class Client extends Model
{
    //
    protected $fillable = [
        'full_name',
        'street',
        'neighborhood',
        'state',
        'zip_code',
        'city',
        'phone',
        'rg',
        'rg_front_photo_path',
        'rg_back_photo_path',
        'cpf',
        'cpf_front_photo_path',
        'cpf_back_photo_path',
        'description',
        'legal_representative',
        'father_name',
        'mother_name'
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime:UTC',
            'updated_at' => 'datetime:UTC',
        ];
    }
}
