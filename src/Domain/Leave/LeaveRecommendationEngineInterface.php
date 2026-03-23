<?php

declare(strict_types=1);

namespace App\Domain\Leave;

use DateTimeImmutable;

interface LeaveRecommendationEngineInterface
{
    public const STRATEGY_BEST_RATIO = 'best_ratio';

    /**
     * @return list<LeaveRecommendation>
     */
    public function recommend(
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        int $budget,
        string $strategy = 'best_ratio',
        int $limit = 10,
    ): array;
}
