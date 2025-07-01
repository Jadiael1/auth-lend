<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ClientUpdateCpfBackPhotoRequest extends FormRequest
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
            'cpf_back_photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'cpf_back_photo.required' => 'The CPF back photo is required.',
            'cpf_back_photo.image' => 'The CPF back photo must be a valid image.',
            'cpf_back_photo.mimes' => 'The CPF back photo must be a file of type: jpeg, jpg, png, webp.',
            'cpf_back_photo.max' => 'The CPF back photo must not exceed 10240 kilobytes.',
        ];
    }
}
