<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductRequest extends FormRequest
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
            'supplier_cost_price' => 'required|numeric|min:0',
            'supplier_coin' => 'required|array',
            'supplier_coin.id' => 'required|exists:coin,id',
            'landing_cost_price' => 'required|numeric|min:0',
            'landing_coin' => 'required|array',
            'landing_coin.id' => 'required|exists:coin,id',
            'retail_price' => 'required|numeric|min:0',
            'promotional_price' => 'required|numeric|lt:retail_price',
            'stock' => 'required|integer|min:0',
            'measure' => 'required|array',
            'measure.id' => 'required|exists:measure,id',
            'serial_tracking' => 'required|string|max:100',
            'dimension_size' => 'required|string|max:50',
            'dimension_weight' => 'required|integer|min:0',
            'sub_brand' => 'required|array',
            'sub_brand.id' => 'required|exists:sub_brand,id',
            'supplier' => 'required|array',
            'supplier.id' => 'required|exists:supplier,id',
        ];
    }
}
