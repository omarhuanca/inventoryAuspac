<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'supplier_cost_price' => $this->supplier_cost_price,
            'supplier_coin' => new CoinResource($this->whenLoaded('supplierCoin')),
            'landing_cost_price' => $this->landing_cost_price,
            'landing_coin' => new CoinResource($this->whenLoaded('landingCoin')),
            'retail_price' => $this->retail_price,
            'promotional_price' => $this->promotional_price,
            'stock' => $this->stock,
            'measure' => new MeasureResource($this->whenLoaded('measure')),
            'serial_tracking' => $this->serial_tracking,
            'dimension_size' => $this->dimension_size,
            'dimension_weight' => $this->dimension_weight,
            'sub_brand' => new SubBrandResource($this->whenLoaded('subBrand')),
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
        ];
    }
}
