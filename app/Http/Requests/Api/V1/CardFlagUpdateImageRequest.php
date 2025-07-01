<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CardFlagUpdateImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()?->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'photo' => ['required', 'file', 'mimes:svg,png', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.required' => 'The photo field is required.',
            'photo.file' => 'The photo must be a valid file.',
            'photo.mimes' => 'Only SVG and PNG formats are allowed.',
            'photo.max' => 'The photo may not be greater than 2MB.',
        ];
    }
}
