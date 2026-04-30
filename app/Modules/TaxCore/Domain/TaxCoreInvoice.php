<?php

namespace App\Modules\TaxCore\Domain;

use Illuminate\Database\Eloquent\Model;

class TaxCoreInvoice extends Model
{
    protected $table = 'taxcore_invoices';

    protected $fillable = [
        'sale_id',
        'taxcore_uid',
        'qr_code',
        'digital_signature',
        'verification_url',
        'raw_response',
    ];

    protected $casts = [
        'raw_response' => 'array',
    ];
}
