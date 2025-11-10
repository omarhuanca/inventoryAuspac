<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;


class StockBuyRequest extends FormRequest
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
            'product' => 'required|array',
            'product.code' => 'required|string|min:2|max:50|regex:/^[A-Za-z0-9_-]+$/',
            'product.supplier_cost_price' => 'required|numeric|min:0',
            'product.supplier_coin' => 'required|array',
            'product.supplier_coin.id' => 'required|exists:coin,id',
            'product.landing_cost_price' => 'required|numeric|min:0',
            'product.landing_coin' => 'required|array',
            'product.landing_coin.id' => 'required|exists:coin,id',
            'product.retail_price' => 'required|numeric|min:0',
            'product.promotional_price' => 'required|numeric|lt:product.retail_price',
            'product.stock' => 'required|integer|min:0',
            'product.measure' => 'required|array',
            'product.measure.id' => 'required|exists:measure,id',
            'product.serial_tracking' => 'required|string|max:100',
            'product.dimension_size' => 'required|string|max:50',
            'product.dimension_weight' => 'required|integer|min:0',
            'product.sub_brand' => 'required|array',
            'product.sub_brand.id' => 'required|exists:sub_brand,id',
            'product.supplier' => 'required|array',
            'product.supplier.id' => 'required|exists:supplier,id',

            'amount' => 'required|integer|min:1',
            'date' => 'required|date_format:Y-m-d',
            'description' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'Amount must be greater than zero.',
            'date.date_format' => 'Date must be in format YYYY-mm-dd.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::error('Validation error.', 422, $validator->errors()->all())
        );
    }
}
