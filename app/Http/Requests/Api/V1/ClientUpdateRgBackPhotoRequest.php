<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ClientUpdateRgBackPhotoRequest extends FormRequest
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
            'rg_back_photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'rg_back_photo.required' => 'The RG back photo is required.',
            'rg_back_photo.image' => 'The RG back photo must be a valid image.',
            'rg_back_photo.mimes' => 'The RG back photo must be a file of type: jpeg, jpg, png, webp.',
            'rg_back_photo.max' => 'The RG back photo must not exceed 10240 kilobytes.',
        ];
    }
}
