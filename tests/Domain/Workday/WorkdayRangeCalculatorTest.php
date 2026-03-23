<?php

declare(strict_types=1);

namespace App\Tests\Domain\Workday;

use App\Domain\Holiday\PolishHolidayProvider;
use App\Domain\Workday\WorkdayRangeCalculator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class WorkdayRangeCalculatorTest extends TestCase
{
    public function testCalculateReturnsExpectedSummaryForJanuary2026(): void
    {
        $calculator = new WorkdayRangeCalculator(new PolishHolidayProvider());

        $summary = $calculator->calculate(
            new DateTimeImmutable('2026-01-01'),
            new DateTimeImmutable('2026-01-31')
        );
        $payload = $summary->toArray();

        self::assertSame('2026-01-01', $payload['from']);
        self::assertSame('2026-01-31', $payload['to']);
        self::assertSame(31, $payload['total_days']);
        self::assertSame(20, $payload['workdays']);
        self::assertSame(11, $payload['non_workdays']);
        self::assertSame(2, $payload['holidays_count']);
    }

    public function testCalculateDoesNotDoubleCountWeekendHoliday(): void
    {
        $calculator = new WorkdayRangeCalculator(new PolishHolidayProvider());

        $summary = $calculator->calculate(
            new DateTimeImmutable('2026-11-01'),
            new DateTimeImmutable('2026-11-01')
        );
        $payload = $summary->toArray();

        self::assertSame(1, $payload['total_days']);
        self::assertSame(0, $payload['workdays']);
        self::assertSame(1, $payload['non_workdays']);
        self::assertSame(1, $payload['holidays_count']);
    }

    public function testCalculateWorksAcrossYearBoundary(): void
    {
        $calculator = new WorkdayRangeCalculator(new PolishHolidayProvider());

        $summary = $calculator->calculate(
            new DateTimeImmutable('2026-12-25'),
            new DateTimeImmutable('2027-01-01')
        );
        $payload = $summary->toArray();

        self::assertSame(8, $payload['total_days']);
        self::assertSame(4, $payload['workdays']);
        self::assertSame(4, $payload['non_workdays']);
        self::assertSame(3, $payload['holidays_count']);
    }
}
