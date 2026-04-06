<?php

namespace App\Presentation\Http\Requests;

use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseFormRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->header('X-User-Id'),
        ]);
    }

    protected function failedValidation(Validator $validator): void
    {
        $apiResponse = app(ApiResponse::class);

        throw new HttpResponseException(
            $apiResponse->validationError($validator->errors())
        );
    }
}
