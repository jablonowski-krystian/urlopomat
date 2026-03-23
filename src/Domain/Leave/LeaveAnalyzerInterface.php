<?php

declare(strict_types=1);

namespace App\Domain\Leave;

use DateTimeImmutable;

interface LeaveAnalyzerInterface
{
    public function analyze(DateTimeImmutable $from, DateTimeImmutable $to): LeaveAnalysis;
}
