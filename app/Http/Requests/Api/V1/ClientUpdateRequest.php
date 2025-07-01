<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ClientUpdateRequest extends FormRequest
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
            'full_name' => ['sometimes', 'string', 'max:255'],
            'street' => ['sometimes', 'nullable', 'string', 'max:255'],
            'neighborhood' => ['sometimes', 'nullable', 'string', 'max:255'],
            'state' => ['sometimes', 'nullable', 'string', 'max:2'],
            'zip_code' => ['sometimes', 'nullable', 'string', 'max:20'],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'rg' => ['sometimes', 'nullable', 'string', 'max:30'],
            'cpf' => ['sometimes', 'nullable', 'string', 'max:14'],
            'description' => ['sometimes', 'nullable', 'string'],
            'legal_representative' => ['sometimes', 'string'],
            'father_name' => ['sometimes', 'string'],
            'mother_name' => ['sometimes', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.string' => 'The full name must be a valid string.',
            'full_name.max' => 'The full name must not exceed 255 characters.',

            'street.string' => 'The street must be a valid string.',
            'street.max' => 'The street must not exceed 255 characters.',

            'neighborhood.string' => 'The neighborhood must be a valid string.',
            'neighborhood.max' => 'The neighborhood must not exceed 255 characters.',

            'state.string' => 'The state must be a valid string.',
            'state.max' => 'The state must not exceed 2 characters.',

            'zip_code.string' => 'The zip code must be a valid string.',
            'zip_code.max' => 'The zip code must not exceed 20 characters.',

            'city.string' => 'The city must be a valid string.',
            'city.max' => 'The city must not exceed 255 characters.',

            'phone.string' => 'The phone must be a valid string.',
            'phone.max' => 'The phone must not exceed 20 characters.',

            'rg.string' => 'The RG must be a valid string.',
            'rg.max' => 'The RG must not exceed 30 characters.',

            'cpf.string' => 'The CPF must be a valid string.',
            'cpf.max' => 'The CPF must not exceed 14 characters.',

            'description.string' => 'The description must be a valid string.',

            'legal_representative.string' => 'The legal representative must be a valid string.',

            'father_name.string' => 'The father\'s name must be a valid string.',

            'mother_name.string' => 'The mother\'s name must be a valid string.',
        ];
    }
}
