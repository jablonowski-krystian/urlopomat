<?php

declare(strict_types=1);

namespace App\Domain\Workday;

use DateTimeImmutable;

interface WorkdayRangeCalculatorInterface
{
    public function calculate(DateTimeImmutable $from, DateTimeImmutable $to): WorkdayRangeSummary;
}
