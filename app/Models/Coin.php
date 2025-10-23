<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Coin",
 *     type="object",
 *     title="Coin",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code", type="string", example="USD"),
 * )
 */
class Coin extends Model
{
    protected $table = 'coin';
    protected $fillable = ['code'];

    public static $codeEmpty = 'Coin code cannot be empty.';
    public static $codeLength = 'Coin code must be between 2 and 10 characters long.';
    public static $codeInvalidChars = 'Coin code contains invalid characters. Allowed: uppercase letters only.';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function at(string $code): Coin
    {
        if ($code === '') {
            throw new \RuntimeException(self::$codeEmpty);
        }

        if (mb_strlen($code) < 2 || mb_strlen($code) > 10) {
            throw new \RuntimeException(self::$codeLength);
        }

        if (!preg_match('/^[A-Z]+$/', $code)) {
            throw new \RuntimeException(self::$codeInvalidChars);
        }

        return new Coin([
            'code' => trim(strtoupper($code)),
        ]);
    }
}
