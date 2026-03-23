<?php

declare(strict_types=1);

namespace App\Domain\Leave\Exception;

use InvalidArgumentException;

final class UnsupportedRecommendationStrategyException extends InvalidArgumentException
{
    public static function fromStrategy(string $strategy): self
    {
        return new self(sprintf('Recommendation strategy "%s" is not supported.', $strategy));
    }
}
