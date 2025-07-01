<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CardFlagInstallmentLimitStoreRequest extends FormRequest
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
            'installments' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('card_flag_installment_limits')
                    ->where(fn($q) => $q->where('card_flag_id', $this->input('card_flag_id')))
            ],
            'min_value' => ['required', 'numeric', 'min:0'],
        ];
    }
}
