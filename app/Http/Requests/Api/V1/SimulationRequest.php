<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class SimulationRequest extends FormRequest
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
            'card_flag_id' => ['required', 'integer', 'exists:card_flags,id'],
            'installments' => ['required', 'integer', 'min:0'],
            'value_type_id' => ['required', 'integer', 'exists:value_types,id'],
            'store_id' => ['required', 'integer', 'exists:stores,id'],
            'amount' => ['required', 'numeric', 'min:1'],
        ];
    }
}
