<?php

declare(strict_types=1);

namespace App\Tests\Domain\Leave;

use App\Domain\Holiday\PolishHolidayProvider;
use App\Domain\Leave\LeaveAnalyzer;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class LeaveAnalyzerTest extends TestCase
{
    public function testAnalyzeReturnsRequiredLeaveForMixedWindow(): void
    {
        $analyzer = new LeaveAnalyzer(new PolishHolidayProvider());

        $analysis = $analyzer->analyze(
            new DateTimeImmutable('2026-01-01'),
            new DateTimeImmutable('2026-01-10')
        );
        $payload = $analysis->toArray();

        self::assertSame(10, $payload['total_days']);
        self::assertSame(5, $payload['leave_days_required']);
        self::assertSame(5, $payload['non_workdays']);
        self::assertSame(2, $payload['holidays_count']);
        self::assertSame(
            ['2026-01-02', '2026-01-05', '2026-01-07', '2026-01-08', '2026-01-09'],
            $payload['leave_dates']
        );
    }

    public function testAnalyzeReturnsZeroLeaveForWeekendOnlyRange(): void
    {
        $analyzer = new LeaveAnalyzer(new PolishHolidayProvider());

        $analysis = $analyzer->analyze(
            new DateTimeImmutable('2026-03-07'),
            new DateTimeImmutable('2026-03-08')
        );
        $payload = $analysis->toArray();

        self::assertSame(2, $payload['total_days']);
        self::assertSame(0, $payload['leave_days_required']);
        self::assertSame(2, $payload['non_workdays']);
        self::assertSame([], $payload['leave_dates']);
    }

    public function testAnalyzeHandlesHolidayAndWeekendOverlap(): void
    {
        $analyzer = new LeaveAnalyzer(new PolishHolidayProvider());

        $analysis = $analyzer->analyze(
            new DateTimeImmutable('2026-12-24'),
            new DateTimeImmutable('2026-12-27')
        );
        $payload = $analysis->toArray();

        self::assertSame(4, $payload['total_days']);
        self::assertSame(1, $payload['leave_days_required']);
        self::assertSame(3, $payload['non_workdays']);
        self::assertSame(['2026-12-24'], $payload['leave_dates']);
        self::assertSame(2, $payload['holidays_count']);
    }
}
