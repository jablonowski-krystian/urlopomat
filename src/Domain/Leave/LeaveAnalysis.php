<?php

declare(strict_types=1);

namespace App\Domain\Leave;

use App\Domain\Holiday\Holiday;
use DateTimeImmutable;

final readonly class LeaveAnalysis
{
    /**
     * @param list<string> $leaveDates
     * @param list<Holiday> $holidays
     */
    public function __construct(
        private DateTimeImmutable $from,
        private DateTimeImmutable $to,
        private int $totalDays,
        private int $leaveDaysRequired,
        private int $nonWorkdays,
        private array $leaveDates,
        private array $holidays,
    ) {
    }

    /**
     * @return array{
     *     from: string,
     *     to: string,
     *     total_days: int,
     *     leave_days_required: int,
     *     non_workdays: int,
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
            'total_days' => $this->totalDays,
            'leave_days_required' => $this->leaveDaysRequired,
            'non_workdays' => $this->nonWorkdays,
            'holidays_count' => count($this->holidays),
            'leave_dates' => $this->leaveDates,
            'holidays' => array_map(
                static fn (Holiday $holiday): array => $holiday->toArray(),
                $this->holidays
            ),
        ];
    }
}
