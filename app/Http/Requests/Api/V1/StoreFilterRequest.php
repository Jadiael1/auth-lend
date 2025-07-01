<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreFilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['sometimes', 'integer'],
            'name' => ['sometimes', 'string'],
            'uf' => ['sometimes', 'string', 'size:2'],
            'city' => ['sometimes', 'string'],
            'neighborhood' => ['sometimes', 'string'],
            'zip_code' => ['sometimes', 'string'],
            'street' => ['sometimes', 'string'],
            'number' => ['sometimes', 'integer'],
            'coordinates' => ['sometimes', 'string'],
            'created_at' => ['sometimes', 'date'],
            'updated_at' => ['sometimes', 'date'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'include_default' => ['sometimes', 'in:true,false,1,0'],
        ];
    }
}
