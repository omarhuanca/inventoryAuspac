<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BundleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'landing_cost_price' => $this->landing_cost_price,
            'landing_coin' => new CoinResource($this->whenLoaded('landingCoin')),
            'retail_price' => $this->retail_price,
            'promotional_price' => $this->promotional_price,
            'products' => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
