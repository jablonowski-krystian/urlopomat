<?php

declare(strict_types=1);

namespace App\Domain\Holiday\Exception;

use InvalidArgumentException;

final class UnsupportedYearException extends InvalidArgumentException
{
    public static function fromYear(int $year, int $minimumYear): self
    {
        return new self(
            \sprintf('Year %d is not supported. Minimum supported year is %d.', $year, $minimumYear)
        );
    }
}
