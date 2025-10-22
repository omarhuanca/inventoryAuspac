<?php

namespace App\Models;

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
class Coin extends Model
{
    protected $fillable = ['code'];

    public const CODE_EMPTY = 'Coin code cannot be empty.';
    public const CODE_LENGTH = 'Coin code must be between 2 and 10 characters long.';
    public const CODE_INVALID_CHARS = 'Coin code contains invalid characters. Allowed: uppercase letters only.';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function at(string $code): Coin
    {
        if ($code === '') {
            throw new \RuntimeException(self::CODE_EMPTY);
        }

        if (mb_strlen($code) < 2 || mb_strlen($code) > 10) {
            throw new \RuntimeException(self::CODE_LENGTH);
        }

        if (!preg_match('/^[A-Z]+$/', $code)) {
            throw new \RuntimeException(self::CODE_INVALID_CHARS);
        }

        return new Coin([
            'code' => trim(strtoupper($code)),
        ]);
    }
}
