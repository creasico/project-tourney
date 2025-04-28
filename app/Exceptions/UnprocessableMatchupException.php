<?php

namespace App\Exceptions;

use InvalidArgumentException;

final class UnprocessableMatchupException extends InvalidArgumentException
{
    public static function singleContinent()
    {
        return new self('Could not process matchup that contains only single continent');
    }
}
