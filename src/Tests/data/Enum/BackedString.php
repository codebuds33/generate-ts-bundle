<?php

namespace App\Test\Enum;

enum BackedString: string
{
    use GetValuesTrait;

    case String = 'String';
    case Another = 'Something';
}
