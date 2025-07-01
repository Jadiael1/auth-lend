<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ClientStoreRequest extends FormRequest
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
            'full_name' => ['required', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:2'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'rg' => ['nullable', 'string', 'max:30'],
            'cpf' => ['nullable', 'string', 'max:14'],
            'description' => ['nullable', 'string'],
            'legal_representative' => ['nullable', 'string'],
            'father_name' => ['nullable', 'string'],
            'mother_name' => ['nullable', 'string'],
            'rg_front_photo' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'rg_back_photo' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'cpf_front_photo' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'cpf_back_photo' => ['nullable', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'The full name is required.',
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

            'rg_front_photo.file' => 'The RG front photo must be a file.',
            'rg_front_photo.image' => 'The RG front photo must be an image.',
            'rg_front_photo.mimes' => 'The RG front photo must be a file of type: jpeg, jpg, png, webp.',
            'rg_front_photo.max' => 'The RG front photo must not exceed 10240 kilobytes.',

            'rg_back_photo.file' => 'The RG back photo must be a file.',
            'rg_back_photo.image' => 'The RG back photo must be an image.',
            'rg_back_photo.mimes' => 'The RG back photo must be a file of type: jpeg, jpg, png, webp.',
            'rg_back_photo.max' => 'The RG back photo must not exceed 10240 kilobytes.',

            'cpf_front_photo.file' => 'The CPF front photo must be a file.',
            'cpf_front_photo.image' => 'The CPF front photo must be an image.',
            'cpf_front_photo.mimes' => 'The CPF front photo must be a file of type: jpeg, jpg, png, webp.',
            'cpf_front_photo.max' => 'The CPF front photo must not exceed 10240 kilobytes.',

            'cpf_back_photo.file' => 'The CPF back photo must be a file.',
            'cpf_back_photo.image' => 'The CPF back photo must be an image.',
            'cpf_back_photo.mimes' => 'The CPF back photo must be a file of type: jpeg, jpg, png, webp.',
            'cpf_back_photo.max' => 'The CPF back photo must not exceed 10240 kilobytes.',
        ];
    }
}
