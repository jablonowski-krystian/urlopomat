<?php

declare(strict_types=1);

namespace App\Domain\Workday;

use App\Domain\Holiday\Holiday;
use DateTimeImmutable;

final readonly class WorkdayRangeSummary
{
    /**
     * @param list<Holiday> $holidays
     */
    public function __construct(
        private DateTimeImmutable $from,
        private DateTimeImmutable $to,
        private int $totalDays,
        private int $workdays,
        private int $nonWorkdays,
        private array $holidays,
    ) {
    }

    /**
     * @return array{
     *     from: string,
     *     to: string,
     *     total_days: int,
     *     workdays: int,
     *     non_workdays: int,
     *     holidays_count: int,
     *     holidays: list<array{code: string, name: string, date: string}>
     * }
     */
    public function toArray(): array
    {
        return [
            'from' => $this->from->format('Y-m-d'),
            'to' => $this->to->format('Y-m-d'),
            'total_days' => $this->totalDays,
            'workdays' => $this->workdays,
            'non_workdays' => $this->nonWorkdays,
            'holidays_count' => count($this->holidays),
            'holidays' => array_map(
                static fn (Holiday $holiday): array => $holiday->toArray(),
                $this->holidays
            ),
        ];
    }
}
