<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ClientUpdateCpfFrontPhotoRequest extends FormRequest
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
            'cpf_front_photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'cpf_front_photo.required' => 'The CPF front photo is required.',
            'cpf_front_photo.image' => 'The CPF front photo must be a valid image.',
            'cpf_front_photo.mimes' => 'The CPF front photo must be a file of type: jpeg, jpg, png, webp.',
            'cpf_front_photo.max' => 'The CPF front photo must not exceed 10240 kilobytes.',
        ];
    }
}
