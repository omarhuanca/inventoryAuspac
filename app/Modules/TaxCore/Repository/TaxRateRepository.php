<?php

namespace App\Modules\TaxCore\Repository;

use App\Modules\TaxCore\Domain\TaxRate;
use Illuminate\Database\Eloquent\Collection;

class TaxRateRepository
{
    public function all(): Collection
    {
        return TaxRate::all();
    }

    public function findByCode(string $code): ?TaxRate
    {
        return TaxRate::where('code', $code)->first();
    }

    public function upsert(array $rates): void
    {
        TaxRate::upsertFromApi($rates);
    }
}
