<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Resources\Api\V1\ResponseResource;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StoreStoreRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'photos' => ['required', 'array', 'min:1', 'max:5'],
            'photos.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'address' => ['required', 'array'],
            'address.uf' => ['required', 'string', 'size:2'],
            'address.city' => ['required', 'string', 'max:100'],
            'address.neighborhood' => ['required', 'string', 'max:100'],
            'address.zip_code' => ['required', 'string', 'max:10', 'regex:/^\d{5}-?\d{3}$/'],
            'address.street' => ['required', 'string', 'max:255'],
            'address.number' => ['required', 'string', 'max:20'],
            'address.coordinates' => ['required', 'string', 'regex:/-?\d+.\d+,\s?-?\d+.\d+/'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The store name is required.',

            'photos.required' => 'At least one photo is required.',
            'photos.array' => 'The photos must be an array.',
            'photos.min' => 'You must upload at least one photo.',
            'photos.max' => 'You can upload a maximum of 5 photos.',
            'photos.*.image' => 'Each photo must be a valid image file.',
            'photos.*.mimes' => 'Each photo must be a file of type: jpeg, png, jpg, webp.',
            'photos.*.max' => 'Each photo may not be greater than 10MB.',

            'address.required' => 'The address information is required.',
            'address.array' => 'The address must be an object with address fields.',
            'address.uf.required' => 'The state (UF) is required.',
            'address.uf.size' => 'The state (UF) must be 2 characters long.',
            'address.city.required' => 'The city is required.',
            'address.neighborhood.required' => 'The neighborhood is required.',
            'address.zip_code.required' => 'The ZIP code is required.',
            'address.street.required' => 'The street is required.',
            'address.number.required' => 'The street number is required.',
            'address.coordinates.required' => 'Coordinates are required.',
            'address.coordinates.regex' => 'Coordinates must be in the format "latitude,longitude" (e.g., -8.12345,-35.12345).',
        ];
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            (new ResponseResource([
                'status'      => 'error',
                'status_code' => Response::HTTP_FORBIDDEN,
                'message'     => 'This action is unauthorized.',
                'data'        => null,
                'errors'      => null,
            ]))->toResponse(request())->setStatusCode(Response::HTTP_FORBIDDEN)
        );
    }
}
