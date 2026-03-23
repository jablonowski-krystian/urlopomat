<?php

declare(strict_types=1);

namespace App\Tests\Domain\Leave;

use App\Domain\Holiday\PolishHolidayProvider;
use App\Domain\Leave\Exception\UnsupportedRecommendationStrategyException;
use App\Domain\Leave\LeaveRecommendationEngine;
use App\Domain\Leave\LeaveRecommendationEngineInterface;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class LeaveRecommendationEngineTest extends TestCase
{
    public function testRecommendReturnsBestRatioRecommendation(): void
    {
        $engine = new LeaveRecommendationEngine(new PolishHolidayProvider());

        $recommendations = $engine->recommend(
            new DateTimeImmutable('2026-05-01'),
            new DateTimeImmutable('2026-05-10'),
            1,
            LeaveRecommendationEngineInterface::STRATEGY_BEST_RATIO,
            5
        );

        self::assertNotEmpty($recommendations);

        $top = $recommendations[0]->toArray();
        self::assertSame('2026-05-01', $top['from']);
        self::assertSame('2026-05-04', $top['to']);
        self::assertSame(4, $top['total_days_off']);
        self::assertSame(1, $top['leave_days_required']);
        self::assertSame(4.0, $top['ratio']);
        self::assertSame(['2026-05-04'], $top['leave_dates']);
    }

    public function testRecommendReturnsEmptyListWhenBudgetIsZero(): void
    {
        $engine = new LeaveRecommendationEngine(new PolishHolidayProvider());

        $recommendations = $engine->recommend(
            new DateTimeImmutable('2026-05-01'),
            new DateTimeImmutable('2026-05-10'),
            0
        );

        self::assertSame([], $recommendations);
    }

    public function testRecommendThrowsForUnsupportedStrategy(): void
    {
        $engine = new LeaveRecommendationEngine(new PolishHolidayProvider());

        $this->expectException(UnsupportedRecommendationStrategyException::class);
        $this->expectExceptionMessage('Recommendation strategy "unknown" is not supported.');

        $engine->recommend(
            new DateTimeImmutable('2026-05-01'),
            new DateTimeImmutable('2026-05-10'),
            2,
            'unknown'
        );
    }
}
