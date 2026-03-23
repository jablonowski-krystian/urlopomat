<?php

declare(strict_types=1);

namespace App\Domain\Holiday;

use App\Domain\Holiday\Exception\UnsupportedYearException;
use DateTimeImmutable;
use RuntimeException;

final class PolishHolidayProvider implements HolidayProviderInterface
{
    public const MIN_SUPPORTED_YEAR = 2000;

    /**
     * @return list<Holiday>
     */
    public function getForYear(int $year): array
    {
        if ($year < self::MIN_SUPPORTED_YEAR) {
            throw UnsupportedYearException::fromYear($year, self::MIN_SUPPORTED_YEAR);
        }

        $easterSunday = $this->calculateEasterSunday($year);

        $holidays = [
            $this->fixedHoliday($year, '01-01', 'new_year', 'Nowy Rok'),
            $this->fixedHoliday($year, '01-06', 'epiphany', 'Trzech Kroli'),
            new Holiday('easter_sunday', 'Niedziela Wielkanocna', $easterSunday),
            new Holiday('easter_monday', 'Poniedzialek Wielkanocny', $easterSunday->modify('+1 day')),
            $this->fixedHoliday($year, '05-01', 'labour_day', 'Swieto Pracy'),
            $this->fixedHoliday($year, '05-03', 'constitution_day', 'Swieto Konstytucji 3 Maja'),
            new Holiday('pentecost', 'Zeslanie Ducha Swietego', $easterSunday->modify('+49 days')),
            new Holiday('corpus_christi', 'Boze Cialo', $easterSunday->modify('+60 days')),
            $this->fixedHoliday($year, '08-15', 'assumption_day', 'Wniebowziecie Najswietszej Maryi Panny'),
            $this->fixedHoliday($year, '11-01', 'all_saints_day', 'Wszystkich Swietych'),
            $this->fixedHoliday($year, '11-11', 'independence_day', 'Narodowe Swieto Niepodleglosci'),
            $this->fixedHoliday($year, '12-25', 'christmas_day', 'Boze Narodzenie (pierwszy dzien)'),
            $this->fixedHoliday($year, '12-26', 'second_day_of_christmas', 'Boze Narodzenie (drugi dzien)'),
        ];

        usort(
            $holidays,
            static fn (Holiday $left, Holiday $right): int => $left->date()->getTimestamp() <=> $right->date()->getTimestamp()
        );

        return $holidays;
    }

    private function fixedHoliday(int $year, string $monthDay, string $code, string $name): Holiday
    {
        return new Holiday($code, $name, $this->createDate($year, $monthDay));
    }

    private function createDate(int $year, string $monthDay): DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', sprintf('%04d-%s', $year, $monthDay));

        if (!$date instanceof DateTimeImmutable) {
            throw new RuntimeException(sprintf('Could not create date for %d-%s.', $year, $monthDay));
        }

        return $date;
    }

    private function calculateEasterSunday(int $year): DateTimeImmutable
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        return $this->createDate($year, sprintf('%02d-%02d', $month, $day));
    }
}
