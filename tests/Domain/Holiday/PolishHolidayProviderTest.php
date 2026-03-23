<?php

declare(strict_types=1);

namespace App\Tests\Domain\Holiday;

use App\Domain\Holiday\Exception\UnsupportedYearException;
use App\Domain\Holiday\Holiday;
use App\Domain\Holiday\PolishHolidayProvider;
use PHPUnit\Framework\TestCase;

final class PolishHolidayProviderTest extends TestCase
{
    public function testGetForYearReturnsAllSupportedPolishPublicHolidays(): void
    {
        $provider = new PolishHolidayProvider();

        $holidays = $provider->getForYear(2026);

        self::assertCount(13, $holidays);
        self::assertSame('2026-01-01', $holidays[0]->date()->format('Y-m-d'));
        self::assertSame('2026-12-26', $holidays[12]->date()->format('Y-m-d'));
    }

    public function testGetForYearCalculatesMovableHolidays(): void
    {
        $provider = new PolishHolidayProvider();

        $holidaysByCode = $this->indexByCode($provider->getForYear(2026));

        self::assertSame('2026-04-05', $holidaysByCode['easter_sunday'] ?? null);
        self::assertSame('2026-04-06', $holidaysByCode['easter_monday'] ?? null);
        self::assertSame('2026-05-24', $holidaysByCode['pentecost'] ?? null);
        self::assertSame('2026-06-04', $holidaysByCode['corpus_christi'] ?? null);
    }

    public function testGetForYearThrowsForUnsupportedYear(): void
    {
        $provider = new PolishHolidayProvider();

        $this->expectException(UnsupportedYearException::class);
        $this->expectExceptionMessage('Year 1999 is not supported. Minimum supported year is 2000.');

        $provider->getForYear(1999);
    }

    /**
     * @param list<Holiday> $holidays
     *
     * @return array<string, string>
     */
    private function indexByCode(array $holidays): array
    {
        $result = [];
        foreach ($holidays as $holiday) {
            $result[$holiday->code()] = $holiday->date()->format('Y-m-d');
        }

        return $result;
    }
}
