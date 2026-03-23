<?php

declare(strict_types=1);

namespace App\Domain\Workday;

use App\Domain\Holiday\Holiday;
use App\Domain\Holiday\HolidayProviderInterface;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;

final readonly class WorkdayRangeCalculator implements WorkdayRangeCalculatorInterface
{
    public function __construct(private HolidayProviderInterface $holidayProvider)
    {
    }

    public function calculate(DateTimeImmutable $from, DateTimeImmutable $to): WorkdayRangeSummary
    {
        $from = $from->setTime(0, 0, 0);
        $to = $to->setTime(0, 0, 0);

        /** @var array<string, true> $holidayDates */
        $holidayDates = [];
        /** @var array<string, Holiday> $holidaysInRangeByDate */
        $holidaysInRangeByDate = [];

        $fromYear = (int) $from->format('Y');
        $toYear = (int) $to->format('Y');

        for ($year = $fromYear; $year <= $toYear; $year++) {
            foreach ($this->holidayProvider->getForYear($year) as $holiday) {
                $date = $holiday->date();
                $dateKey = $date->format('Y-m-d');

                $holidayDates[$dateKey] = true;
                if ($date >= $from && $date <= $to) {
                    $holidaysInRangeByDate[$dateKey] = $holiday;
                }
            }
        }

        $totalDays = (int) $from->diff($to)->days + 1;
        $workdays = 0;
        $nonWorkdays = 0;

        foreach ($this->iterateDays($from, $to) as $date) {
            $dayOfWeek = (int) $date->format('N');
            $dateKey = $date->format('Y-m-d');

            $isWeekend = $dayOfWeek >= 6;
            $isHoliday = isset($holidayDates[$dateKey]);

            if ($isWeekend || $isHoliday) {
                $nonWorkdays++;
                continue;
            }

            $workdays++;
        }

        /** @var list<Holiday> $holidaysInRange */
        $holidaysInRange = array_values($holidaysInRangeByDate);

        usort(
            $holidaysInRange,
            static fn (Holiday $left, Holiday $right): int => $left->date()->getTimestamp() <=> $right->date()->getTimestamp()
        );

        return new WorkdayRangeSummary(
            $from,
            $to,
            $totalDays,
            $workdays,
            $nonWorkdays,
            $holidaysInRange
        );
    }

    /**
     * @return iterable<DateTimeImmutable>
     */
    private function iterateDays(DateTimeImmutable $from, DateTimeImmutable $to): iterable
    {
        $endExclusive = $to->modify('+1 day');

        return new DatePeriod($from, new DateInterval('P1D'), $endExclusive);
    }
}
