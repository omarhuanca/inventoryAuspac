<?php

namespace App\Modules\TaxCore\Domain;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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
