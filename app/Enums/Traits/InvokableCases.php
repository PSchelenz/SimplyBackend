<?php

namespace App\Enums\Traits;

use App\Enums\Exceptions\UndefinedCaseException;
use BackedEnum;

trait InvokableCases
{
    public function __invoke(): mixed
    {
        return $this instanceof BackedEnum ? $this->value : $this->name;
    }

    public static function __callStatic(string $name, array $arguments)
    {
        $cases = static::cases();

        foreach ($cases as $case) {
            if ($case->name === $name) {
                return $case instanceof BackedEnum ? $case->value : $case->name;
            }
        }

        throw new UndefinedCaseException(static::class, $name);
    }
}
