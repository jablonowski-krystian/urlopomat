<?php

declare(strict_types=1);

namespace App\Domain\Holiday;

interface HolidayProviderInterface
{
    /**
     * @return list<Holiday>
     */
    public function getForYear(int $year): array;
}
