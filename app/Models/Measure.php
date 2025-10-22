<?php

namespace App\Models;

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
    protected $fillable = ['code'];

    public const CODE_EMPTY = 'Measure code cannot be empty.';
    public const CODE_LENGTH = 'Measure code must be between 1 and 10 characters long.';
    public const CODE_INVALID_CHARS = 'Measure code contains invalid characters. Allowed: uppercase letters only.';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public static function at(string $code): Measure
    {
        if ($code === '') {
            throw new \RuntimeException(self::CODE_EMPTY);
        }

        if (mb_strlen($code) < 1 || mb_strlen($code) > 10) {
            throw new \RuntimeException(self::CODE_LENGTH);
        }

        if (!preg_match('/^[A-Za-z]+$/', $code)) {
            throw new \RuntimeException(self::CODE_INVALID_CHARS);
        }

        return new Measure([
            'code' => trim(strtoupper($code)),
        ]);
    }
}
