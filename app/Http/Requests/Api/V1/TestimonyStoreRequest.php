<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TestimonyStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; //Auth::check() && Auth::user()?->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'uf' => ['required', 'string', 'size:2'],
            'message' => ['required', 'string', 'max:3000'],
            'photo' => ['sometimes', 'image', 'mimes:jpeg,png,webp', 'max:2048'], // 2MB
        ];
    }

    public function messages(): array
    {
        return [
            'photo.image' => 'The uploaded file must be an image.',
            'photo.mimes' => 'Only JPEG, PNG or WEBP images are allowed.',
            'photo.max' => 'The image size must not exceed 2MB.',
        ];
    }
}
