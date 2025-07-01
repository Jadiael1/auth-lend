<?php

namespace App\GraphQL\Validators;

use Nuwave\Lighthouse\Validation\Validator;

final class UserValidator extends Validator
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'id' => ['prohibits:email', 'integer', 'min:1'],
            'email' => ['prohibits:id', 'email'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'id.prohibits' => 'The ID field cannot be sent together with the email field.',
            'id.integer' => 'The ID field must be an integer.',
            'id.min' => 'The ID must be at least 1.',
            'email.prohibits' => 'The email field cannot be sent together with the ID field.',
            'email.email' => 'The email provided is not in a valid format.',
        ];
    }
}
