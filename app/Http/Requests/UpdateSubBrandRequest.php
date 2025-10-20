<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubBrandRequest extends FormRequest
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
            'brand' => 'sometimes|array',
            'brand.id' => 'required_with:brand|exists:brands,id',
            'brand.code' => 'required_with:brand|string',
        ];
    }
}
