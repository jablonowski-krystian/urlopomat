<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\WorkdaysRangeController;
use App\Domain\Holiday\PolishHolidayProvider;
use App\Domain\Workday\WorkdayRangeCalculator;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

final class WorkdaysRangeControllerTest extends TestCase
{
    public function testInvokeReturnsSummaryForValidRange(): void
    {
        $controller = $this->createController();

        $response = $controller(new Request(['from' => '2026-01-01', 'to' => '2026-01-31']));
        $payload = $this->decodeJsonResponse($response->getContent());

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('PL', $payload['country'] ?? null);
        self::assertSame(20, $payload['workdays'] ?? null);
        self::assertSame(11, $payload['non_workdays'] ?? null);
    }

    public function testInvokeReturnsBadRequestWhenFromIsMissing(): void
    {
        $controller = $this->createController();

        $response = $controller(new Request(['to' => '2026-01-31']));
        $payload = $this->decodeJsonResponse($response->getContent());

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('missing_from', $payload['error']['code'] ?? null);
    }

    public function testInvokeReturnsBadRequestWhenToIsInvalid(): void
    {
        $controller = $this->createController();

        $response = $controller(new Request(['from' => '2026-01-01', 'to' => '2026/01/31']));
        $payload = $this->decodeJsonResponse($response->getContent());

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('invalid_to', $payload['error']['code'] ?? null);
    }

    public function testInvokeReturnsBadRequestWhenRangeOrderIsInvalid(): void
    {
        $controller = $this->createController();

        $response = $controller(new Request(['from' => '2026-02-01', 'to' => '2026-01-31']));
        $payload = $this->decodeJsonResponse($response->getContent());

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('invalid_range', $payload['error']['code'] ?? null);
    }

    public function testInvokeReturnsBadRequestWhenRangeIsTooLarge(): void
    {
        $controller = $this->createController();

        $response = $controller(new Request(['from' => '2026-01-01', 'to' => '2027-01-02']));
        $payload = $this->decodeJsonResponse($response->getContent());

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('range_too_large', $payload['error']['code'] ?? null);
    }

    public function testInvokeReturnsBadRequestWhenRangeStartsBeforeSupportedYear(): void
    {
        $controller = $this->createController();

        $response = $controller(new Request(['from' => '1999-12-31', 'to' => '2000-01-03']));
        $payload = $this->decodeJsonResponse($response->getContent());

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('unsupported_year', $payload['error']['code'] ?? null);
    }

    private function createController(): WorkdaysRangeController
    {
        return new WorkdaysRangeController(new WorkdayRangeCalculator(new PolishHolidayProvider()));
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
