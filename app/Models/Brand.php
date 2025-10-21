<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $fillable = ['code'];

    public const CODE_EMPTY = 'Brand code cannot be empty.';
    public const CODE_LENGTH = 'Brand code must be between 2 and 50 characters long.';
    public const CODE_INVALID_CHARS = 'Brand code contains invalid characters. Allowed: letters, numbers, hyphen and underscore';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function at(string $code): Brand
    {
        if ($code === '')
        {
            throw new \RuntimeException(self::CODE_EMPTY);
        }

        if (mb_strlen($code) < 2 || mb_strlen($code) > 50) {
            throw new \RuntimeException(self::CODE_LENGTH);
        }

        if (!preg_match('/^[A-Za-z0-9_-]+$/', $code)) {
            throw new \RuntimeException(self::CODE_INVALID_CHARS);
        }

        return new Brand([
            'code' => trim($code),
        ]);
    }

    public function subBrands()
    {
        return $this->hasMany(SubBrand::class);
    }
}
