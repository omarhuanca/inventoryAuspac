<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SubBrandRequest extends FormRequest
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
            'code' => 'required|string|min:2|max:50|regex:/^[A-Za-z0-9_-]+$/',
            'brand' => 'required|array',
            'brand.id' => 'required|exists:brand,id',
            'brand.code' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'brand.id.exists' => 'You have not selected a valid brand.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::error('Validation error.', 422, $validator->errors()->all())
        );
    }
}
