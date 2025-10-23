<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Supplier",
 *     type="object",
 *     title="Supplier",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Supplier 1"),
 * )
 */
class Supplier extends Model
{
    protected $table = 'supplier';
    protected $fillable = ['name'];

    public static $nameEmpty = 'Supplier name cannot be empty.';
    public static $nameLength = 'Supplier name must be between 2 and 150 characters long.';
    public static $nameInvalid = 'Supplier name contains invalid characters. Allowed: letters, numbers, spaces, hyphen, underscore, ampersand, comma and period.';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

        public static function at(string $name): Supplier
        {
            if ($name === '') {
                throw new \RuntimeException(self::$nameEmpty);
            }

            if (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
                throw new \RuntimeException(self::$nameLength);
            }

            if (!preg_match('/^[\p{L}0-9\s\-\_&.,]+$/u', $name)) {
                throw new \RuntimeException(self::$nameInvalid);
            }

            return new Supplier(['name' => trim($name)]);
        }
}
