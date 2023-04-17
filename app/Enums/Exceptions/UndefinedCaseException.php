<?php

namespace App\Enums\Exceptions;

use Error;

class UndefinedCaseException extends Error
{
    public function __construct(string $enum, string $case)
    {
        parent::__construct("Undefined case '$case' in enum '$enum'");
    }
}
