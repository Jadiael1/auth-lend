<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Resources\Api\V1\ResponseResource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class InterestConfigurationStoreRequest extends FormRequest
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
        $cardFlagId = $this->input('card_flag_id');
        $storeId = $this->input('store_id');
        $installments = $this->input('installments');
        $valueTypeId = $this->input('value_type_id');

        return [
            'card_flag_id' => ['required', 'exists:card_flags,id'],
            'store_id' => ['required', 'exists:stores,id'],
            'installments' => ['required', 'integer', 'min:0', Rule::unique('interest_configurations')->where(fn($query) => $query->where('card_flag_id', $cardFlagId)->where('store_id', $storeId)->where('installments', $installments)->where('value_type_id', $valueTypeId))],
            'value_type_id' => ['required', 'exists:value_types,id'],
            'interest_rate' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'card_flag_id.required' => 'The card flag ID is required.',
            'card_flag_id.exists' => 'The selected card flag does not exist.',
            'store_id.required' => 'The store ID is required.',
            'store_id.exists' => 'The selected store does not exist.',
            'installments.required' => 'The installments field is required.',
            'installments.integer' => 'The installments field must be an integer.',
            'installments.min' => 'The installments must be at least 1.',
            'installments.unique' => 'An interest configuration with this card flag, store, value type and installment already exists.',
            'value_type_id.required' => 'The value type ID is required.',
            'value_type_id.exists' => 'The selected value type does not exist.',
            'interest_rate.required' => 'The interest rate is required.',
            'interest_rate.numeric' => 'The interest rate must be a valid number.',
            'interest_rate.min' => 'The interest rate must be at least 0.',
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            (new ResponseResource([
                'status'      => 'error',
                'status_code' => Response::HTTP_FORBIDDEN,
                'message'     => 'This action is unauthorized.',
                'data'        => null,
                'errors'      => null,
            ]))->toResponse($this)->setStatusCode(Response::HTTP_FORBIDDEN)
        );
    }
}
