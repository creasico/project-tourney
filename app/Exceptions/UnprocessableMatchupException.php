<?php

declare(strict_types=1);

namespace App\Exceptions;

use InvalidArgumentException;

final class UnprocessableMatchupException extends InvalidArgumentException
{
    public function __construct(
        string $message,
        public readonly array $athletes = [],
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }
}
