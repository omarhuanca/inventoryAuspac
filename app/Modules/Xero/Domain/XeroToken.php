<?php

namespace App\Modules\Xero\Domain;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class XeroToken extends Model
{
    protected $table = 'xero_tokens';

    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'access_token',
        'refresh_token',
        'id_token',
        'tenant_id',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function hasExpired(): bool
    {
        return Carbon::now()->greaterThanOrEqualTo($this->expires_at);
    }
}
