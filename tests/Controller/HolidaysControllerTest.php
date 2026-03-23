<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\HolidaysController;
use App\Domain\Holiday\PolishHolidayProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

final class HolidaysControllerTest extends TestCase
{
    public function testInvokeReturnsHolidayListForValidYear(): void
    {
        $controller = new HolidaysController(new PolishHolidayProvider());

        $response = $controller(new Request(['year' => '2026']));
        $payload = $this->decodeJsonResponse($response->getContent());

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('PL', $payload['country'] ?? null);
        self::assertSame(2026, $payload['year'] ?? null);

        $holidays = $payload['holidays'] ?? null;
        self::assertIsArray($holidays);
        self::assertCount(13, $holidays);
    }

    public function testInvokeReturnsBadRequestWhenYearIsMissing(): void
    {
        $controller = new HolidaysController(new PolishHolidayProvider());

        $response = $controller(new Request());
        $payload = $this->decodeJsonResponse($response->getContent());

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('missing_year', $payload['error']['code'] ?? null);
    }

    public function testInvokeReturnsBadRequestWhenYearIsInvalid(): void
    {
        $controller = new HolidaysController(new PolishHolidayProvider());

        $response = $controller(new Request(['year' => 'invalid']));
        $payload = $this->decodeJsonResponse($response->getContent());

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('invalid_year', $payload['error']['code'] ?? null);
    }

    public function testInvokeReturnsBadRequestWhenYearIsNotSupported(): void
    {
        $controller = new HolidaysController(new PolishHolidayProvider());

        $response = $controller(new Request(['year' => '1999']));
        $payload = $this->decodeJsonResponse($response->getContent());

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('unsupported_year', $payload['error']['code'] ?? null);
    }

    /**
     * @return array<mixed>
     */
    private function decodeJsonResponse(string|false $content): array
    {
        if (!is_string($content)) {
            throw new RuntimeException('Response content should be a valid JSON string.');
        }

        $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new RuntimeException('Decoded JSON should be an array.');
        }

        return $decoded;
    }
}
