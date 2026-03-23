<?php

declare(strict_types=1);

namespace App\Domain\Leave;

use App\Domain\Holiday\Holiday;
use DateTimeImmutable;

final readonly class LeaveRecommendation
{
    /**
     * @param list<string> $leaveDates
     * @param list<Holiday> $holidays
     */
    public function __construct(
        private DateTimeImmutable $from,
        private DateTimeImmutable $to,
        private int $totalDaysOff,
        private int $leaveDaysRequired,
        private float $ratio,
        private array $leaveDates,
        private array $holidays,
    ) {
    }

    /**
     * @return array{
     *     from: string,
     *     to: string,
     *     total_days_off: int,
     *     leave_days_required: int,
     *     ratio: float,
     *     holidays_count: int,
     *     leave_dates: list<string>,
     *     holidays: list<array{code: string, name: string, date: string}>
     * }
     */
    public function toArray(): array
    {
        return [
            'from' => $this->from->format('Y-m-d'),
            'to' => $this->to->format('Y-m-d'),
            'total_days_off' => $this->totalDaysOff,
            'leave_days_required' => $this->leaveDaysRequired,
            'ratio' => round($this->ratio, 4),
            'holidays_count' => count($this->holidays),
            'leave_dates' => $this->leaveDates,
            'holidays' => array_map(
                static fn (Holiday $holiday): array => $holiday->toArray(),
                $this->holidays
            ),
        ];
    }
}
