<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class InterestConfigurationUpdateRequest extends FormRequest
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
            'card_flag_id' => ['required', 'integer', 'exists:card_flags,id'],
            'installments' => ['required', 'integer', 'min:1'],
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'value_type_id' => ['required', 'integer', 'exists:value_types,id'],
            'interest_rate' => ['required', 'numeric', 'min:0'],
        ];
    }
}
