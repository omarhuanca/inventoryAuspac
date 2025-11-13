<?php

namespace App\Modules\Measure\Domain;

use App\Modules\Product\Domain\Product;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Measure",
 *     type="object",
 *     title="Measure",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code", type="string", example="UNIT"),
 * )
 */
class Measure extends Model
{
    protected $table = 'measure';
    protected $fillable = ['code'];

    public static $codeEmpty = 'Measure code cannot be empty.';
    public static $codeLength = 'Measure code must be between 1 and 10 characters long.';
    public static $codeInvalidChars = 'Measure code contains invalid characters. Allowed: uppercase letters only.';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function at(string $code): Measure
    {
        if ($code === '') {
            throw new \RuntimeException(self::$codeEmpty);
        }

        if (mb_strlen($code) < 1 || mb_strlen($code) > 10) {
            throw new \RuntimeException(self::$codeLength);
        }

        if (!preg_match('/^[A-Za-z]+$/', $code)) {
            throw new \RuntimeException(self::$codeInvalidChars);
        }

        return new Measure([
            'code' => trim(strtoupper($code)),
        ]);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
