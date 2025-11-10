<?php

namespace App\Modules\Brand\Domain;

use App\Modules\SubBrand\Domain\SubBrand;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Brand",
 *     type="object",
 *     title="Brand",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code", type="string", example="BS"),
 * )
 */
class Brand extends Model
{
    protected $table = 'brand';
    protected $fillable = ['code'];

    public static $codeEmpty = 'Brand code cannot be empty.';
    public static $codeLength = 'Brand code must be between 2 and 50 characters long.';
    public static $codeInvalidChars = 'Brand code contains invalid characters. Allowed: letters, numbers, hyphen and underscore';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function at(string $code): Brand
    {
        if ($code === '')
        {
            throw new \RuntimeException(self::$codeEmpty);
        }

        if (mb_strlen($code) < 2 || mb_strlen($code) > 50) {
            throw new \RuntimeException(self::$codeLength);
        }

        if (!preg_match('/^[A-Za-z0-9_-]+$/', $code)) {
            throw new \RuntimeException(self::$codeInvalidChars);
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
