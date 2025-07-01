<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Resources\Api\V1\ResponseResource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CardFlagDestroyRequest extends FormRequest
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
            //
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
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
