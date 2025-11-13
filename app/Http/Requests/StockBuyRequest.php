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
        $productRules = collect((new ProductRequest())->rules())
            ->mapWithKeys(function ($rule, $key) {
                $update = fn($r) => is_string($r) && !preg_match('/\b(exists|unique):/i', $r)
                    ? preg_replace('/:(?=[a-zA-Z_]\w*)/', ':product.', $r)
                    : $r;

                $rule = is_array($rule) ? array_map($update, $rule) : $update($rule);
                return ["product.$key" => $rule];
            })
            ->toArray();

        return array_merge($productRules, [
            'amount' => 'required|integer|min:1',
            'date' => 'required|date_format:Y-m-d',
            'description' => 'required|string|max:255',
        ]);
    }

    public function messages(): array
    {
        $productMessages = collect((new ProductRequest())->messages())
            ->mapWithKeys(fn($message, $key) => ["product.$key" => $message])
            ->toArray();

        return array_merge($productMessages, [
            'amount.min' => 'Amount must be greater than zero.',
            'date.date_format' => 'Date must be in format YYYY-mm-dd.',
        ]);
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::error('Validation error.', 422, $validator->errors()->all())
        );
    }
}
