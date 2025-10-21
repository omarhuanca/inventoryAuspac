<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name'];

    public const NAME_EMPTY = 'Supplier name cannot be empty.';
    public const NAME_LENGTH = 'Supplier name must be between 2 and 150 characters long.';
    public const NAME_INVALID = 'Supplier name contains invalid characters. Allowed: letters, numbers, spaces, hyphen, underscore, ampersand, comma and period.';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

        public static function at(string $name): Supplier
        {
            if ($name === '') {
                throw new \RuntimeException(Supplier::NAME_EMPTY);
            }

            if (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
                throw new \RuntimeException(Supplier::NAME_LENGTH);
            }

            if (!preg_match('/^[\p{L}0-9\s\-\_&.,]+$/u', $name)) {
                throw new \RuntimeException(Supplier::NAME_INVALID);
            }

            return new Supplier(['name' => trim($name)]);
        }
}
