<?php

declare(strict_types=1);

namespace App\Domain\Holiday;

use DateTimeImmutable;

final readonly class Holiday
{
    public function __construct(
        private string $code,
        private string $name,
        private DateTimeImmutable $date,
    ) {
    }

    public function code(): string
    {
        return $this->code;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function date(): DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @return array{code: string, name: string, date: string}
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'date' => $this->date->format('Y-m-d'),
        ];
    }
}
