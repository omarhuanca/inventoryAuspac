<?php

namespace App\Models;

use App\Constants\SupplierMessages;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function at(string $name): Supplier
    {
        $name = trim($name);

        if ($name === '') {
            throw new \RuntimeException(SupplierMessages::NAME_EMPTY);
        }

        if (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
            throw new \RuntimeException(SupplierMessages::NAME_LENGTH);
        }

        if (!preg_match('/^[\p{L}0-9\s\-\_&.,]+$/u', $name)) {
            throw new \RuntimeException(SupplierMessages::NAME_INVALID);
        }

        return new Supplier(['name' => $name]);
    }
}
