<?php

namespace App\Modules\TaxCore\Domain;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="TaxRate",
 *     type="object",
 *     title="TaxRate",
 *     description="Tax rate synced from TaxCore E-SDC environment parameters.",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code", type="string", example="A"),
 *     @OA\Property(property="name", type="string", example="Standard Rate"),
 *     @OA\Property(property="rate", type="number", format="float", example=0.2),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="synced_at", type="string", format="date-time")
 * )
 */
class TaxRate extends Model
{
    protected $table = 'tax_rates';

    protected $fillable = [
        'code',
        'name',
        'rate',
        'description',
        'synced_at',
    ];

    protected $casts = [
        'rate'      => 'decimal:4',
        'synced_at' => 'datetime',
    ];

    public static function upsertFromApi(array $rates): void
    {
        $now = Carbon::now();

        foreach ($rates as $rate) {
            static::updateOrCreate(
                ['code' => $rate['code']],
                [
                    'name' => $rate['name'] ?? $rate['code'],
                    'rate' => $rate['rate'] ?? $rate['value'] ?? 0,
                    'description' => $rate['description'] ?? null,
                    'synced_at' => $now,
                ]
            );
        }
    }
}
