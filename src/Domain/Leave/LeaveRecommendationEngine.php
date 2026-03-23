<?php

declare(strict_types=1);

namespace App\Domain\Leave;

use App\Domain\Holiday\Holiday;
use App\Domain\Holiday\HolidayProviderInterface;
use App\Domain\Leave\Exception\UnsupportedRecommendationStrategyException;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;

final readonly class LeaveRecommendationEngine implements LeaveRecommendationEngineInterface
{
    public function __construct(private HolidayProviderInterface $holidayProvider)
    {
    }

    public function recommend(
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        int $budget,
        string $strategy = LeaveRecommendationEngineInterface::STRATEGY_BEST_RATIO,
        int $limit = 10,
    ): array {
        if ($strategy !== LeaveRecommendationEngineInterface::STRATEGY_BEST_RATIO) {
            throw UnsupportedRecommendationStrategyException::fromStrategy($strategy);
        }

        if ($budget <= 0 || $limit <= 0) {
            return [];
        }

        $from = $from->setTime(0, 0, 0);
        $to = $to->setTime(0, 0, 0);

        /** @var array<string, Holiday> $holidaysByDate */
        $holidaysByDate = [];
        $fromYear = (int) $from->format('Y');
        $toYear = (int) $to->format('Y');

        for ($year = $fromYear; $year <= $toYear; $year++) {
            foreach ($this->holidayProvider->getForYear($year) as $holiday) {
                $date = $holiday->date();
                if ($date < $from || $date > $to) {
                    continue;
                }

                $holidaysByDate[$date->format('Y-m-d')] = $holiday;
            }
        }

        $days = [];
        foreach ($this->iterateDays($from, $to) as $date) {
            $dateKey = $date->format('Y-m-d');
            $isWeekend = (int) $date->format('N') >= 6;
            $isHoliday = isset($holidaysByDate[$dateKey]);
            $isWorkday = !$isWeekend && !$isHoliday;

            $days[] = [
                'date' => $date,
                'date_key' => $dateKey,
                'is_workday' => $isWorkday,
            ];
        }

        /** @var list<int> $prefixWorkdays */
        $prefixWorkdays = [0];
        foreach ($days as $day) {
            $prefixWorkdays[] = end($prefixWorkdays) + (($day['is_workday'] === true) ? 1 : 0);
        }

        /** @var list<array{start: int, end: int, leave: int, total: int, ratio: float}> $candidates */
        $candidates = [];
        $dayCount = count($days);

        for ($start = 0; $start < $dayCount; $start++) {
            for ($end = $start; $end < $dayCount; $end++) {
                $leaveDaysRequired = $prefixWorkdays[$end + 1] - $prefixWorkdays[$start];

                if ($leaveDaysRequired > $budget) {
                    break;
                }

                if ($leaveDaysRequired === 0) {
                    continue;
                }

                $totalDaysOff = $end - $start + 1;
                $candidates[] = [
                    'start' => $start,
                    'end' => $end,
                    'leave' => $leaveDaysRequired,
                    'total' => $totalDaysOff,
                    'ratio' => $totalDaysOff / $leaveDaysRequired,
                ];
            }
        }

        usort(
            $candidates,
            function (array $left, array $right) use ($days): int {
                $ratioCmp = $right['ratio'] <=> $left['ratio'];
                if ($ratioCmp !== 0) {
                    return $ratioCmp;
                }

                $totalCmp = $right['total'] <=> $left['total'];
                if ($totalCmp !== 0) {
                    return $totalCmp;
                }

                $leaveCmp = $left['leave'] <=> $right['leave'];
                if ($leaveCmp !== 0) {
                    return $leaveCmp;
                }

                return strcmp($days[$left['start']]['date_key'], $days[$right['start']]['date_key']);
            }
        );

        $candidates = array_slice($candidates, 0, $limit);

        /** @var list<LeaveRecommendation> $recommendations */
        $recommendations = [];
        foreach ($candidates as $candidate) {
            $start = $candidate['start'];
            $end = $candidate['end'];

            $leaveDates = [];
            $holidays = [];

            for ($index = $start; $index <= $end; $index++) {
                $day = $days[$index];
                $dateKey = $day['date_key'];

                if ($day['is_workday'] === true) {
                    $leaveDates[] = $dateKey;
                }

                if (isset($holidaysByDate[$dateKey])) {
                    $holidays[] = $holidaysByDate[$dateKey];
                }
            }

            $recommendations[] = new LeaveRecommendation(
                $days[$start]['date'],
                $days[$end]['date'],
                $candidate['total'],
                $candidate['leave'],
                $candidate['ratio'],
                $leaveDates,
                $holidays
            );
        }

        return $recommendations;
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
