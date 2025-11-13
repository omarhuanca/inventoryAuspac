<?php

namespace App\Modules\StockBuy\Domain;

use Illuminate\Database\Eloquent\Model;

abstract class StockTransaction extends Model
{
    protected $fillable = ['product_id', 'amount', 'date'];
}
