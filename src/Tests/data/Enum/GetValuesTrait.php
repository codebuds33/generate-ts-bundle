<?php

namespace App\Test\Enum;

trait GetValuesTrait
{
    public static function getValues(): array
    {
        $cases = self::cases();

        return array_map(static fn ($case) => $case->value, $cases);
    }
}
