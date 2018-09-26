<?php

namespace App\Enums;

/**
 * Class ErrorCodeEnum
 */
abstract class ErrorCodeEnum
{
    public const CSV_INVALID = 'CSV_INVALID';
    public const MAP_MODEL_NOT_FOUND = 'MAP_MODEL_NOT_FOUND';
    public const MAP_MODEL_NOT_PASSED = 'MAP_MODEL_NOT_PASSED';
    public const MAPPINGS_INVALID = 'MAPPINGS_INVALID';
}
