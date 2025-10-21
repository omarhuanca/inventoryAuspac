<?php

namespace App\Constants;

class SubBrandMessages
{
    public const CODE_EMPTY = 'SubBrand code cannot be empty.';
    public const CODE_LENGTH = 'SubBrand code must be between 2 and 50 characters long.';
    public const CODE_INVALID = 'SubBrand code contains invalid characters. Allowed: letters, numbers, hyphen and underscore.';
    public const BRAND_INVALID = 'SubBrand must be associated with a valid Brand instance.';
}
