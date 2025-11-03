<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BundleRequest extends FormRequest
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
            'landing_cost_price' => 'required|numeric|min:0',
            'landing_coin' => 'required|array',
            'landing_coin.id' => 'required|exists:coin,id',
            'retail_price' => 'required|numeric|min:0',
            'promotional_price' => 'required|numeric|min:0|lt:retail_price',

            'products' => 'required|array|min:2',
            'products.*.code' => 'required|string|min:2|max:50|regex:/^[A-Za-z0-9_-]+$/',
            'products.*.supplier_cost_price' => 'required|numeric|min:0',
            'products.*.supplier_coin' => 'required|array',
            'products.*.supplier_coin.id' => 'required|exists:coin,id',
            'products.*.landing_cost_price' => 'required|numeric|min:0',
            'products.*.landing_coin' => 'required|array',
            'products.*.landing_coin.id' => 'required|exists:coin,id',
            'products.*.retail_price' => 'required|numeric|min:0',
            'products.*.promotional_price' => 'required|numeric|lt:products.*.retail_price',
            'products.*.stock' => 'required|integer|min:0',
            'products.*.measure' => 'required|array',
            'products.*.measure.id' => 'required|exists:measure,id',
            'products.*.serial_tracking' => 'required|string|max:100',
            'products.*.dimension_size' => 'required|string|max:50',
            'products.*.dimension_weight' => 'required|integer|min:0',
            'products.*.sub_brand' => 'required|array',
            'products.*.sub_brand.id' => 'required|exists:sub_brand,id',
            'products.*.supplier' => 'required|array',
            'products.*.supplier.id' => 'required|exists:supplier,id',
        ];
    }

    public function messages(): array
    {
        return [
            'landing_coin.id.exists' => 'Bundle: You have not selected a valid landing coin.',
            'products.required' => 'A bundle must contain at least two products.',
            'products.min' => 'A bundle must contain at least two products.',

            'products.*.supplier_coin.id.exists' => 'You have not selected a valid supplier coin.',
            'products.*.landing_coin.id.exists' => 'You have not selected a valid landing coin.',
            'products.*.measure.id.exists' => 'You have not selected a valid measure.',
            'products.*.sub_brand.id.exists' => 'You have not selected a valid sub brand.',
            'products.*.supplier.id.exists' => 'You have not selected a valid supplier.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::error('Validation error.', 422, $validator->errors()->all())
        );
    }
}
