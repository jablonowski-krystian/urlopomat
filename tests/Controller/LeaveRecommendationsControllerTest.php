<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\LeaveRecommendationsController;
use App\Domain\Holiday\PolishHolidayProvider;
use App\Domain\Leave\LeaveRecommendationEngine;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

final class LeaveRecommendationsControllerTest extends TestCase
{
    public function testInvokeReturnsRecommendationsForValidQuery(): void
    {
        $controller = $this->createController();

        $response = $controller(
            new Request(
                [
                    'from' => '2026-05-01',
                    'to' => '2026-05-10',
                    'budget' => '1',
                    'strategy' => 'best_ratio',
                ]
            )
        );
        $payload = $this->decodeJsonResponse($response->getContent());

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('PL', $payload['country'] ?? null);
        self::assertSame('best_ratio', $payload['strategy'] ?? null);
        self::assertSame(1, $payload['budget'] ?? null);
        self::assertNotEmpty($payload['recommendations'] ?? []);
    }

    public function testInvokeReturnsBadRequestWhenBudgetIsMissing(): void
    {
        $controller = $this->createController();

        $response = $controller(
            new Request(['from' => '2026-05-01', 'to' => '2026-05-10', 'strategy' => 'best_ratio'])
        );
        $payload = $this->decodeJsonResponse($response->getContent());

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('missing_budget', $payload['error']['code'] ?? null);
    }

    public function testInvokeReturnsBadRequestWhenBudgetIsInvalid(): void
    {
        $controller = $this->createController();

        $response = $controller(
            new Request(['from' => '2026-05-01', 'to' => '2026-05-10', 'budget' => '-2', 'strategy' => 'best_ratio'])
        );
        $payload = $this->decodeJsonResponse($response->getContent());

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('invalid_budget', $payload['error']['code'] ?? null);
    }

    public function testInvokeReturnsBadRequestWhenStrategyIsUnsupported(): void
    {
        $controller = $this->createController();

        $response = $controller(
            new Request(['from' => '2026-05-01', 'to' => '2026-05-10', 'budget' => '2', 'strategy' => 'unknown'])
        );
        $payload = $this->decodeJsonResponse($response->getContent());

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('invalid_strategy', $payload['error']['code'] ?? null);
    }

    public function testInvokeReturnsBadRequestWhenRangeIsTooLarge(): void
    {
        $controller = $this->createController();

        $response = $controller(
            new Request(['from' => '2026-01-01', 'to' => '2027-01-02', 'budget' => '5', 'strategy' => 'best_ratio'])
        );
        $payload = $this->decodeJsonResponse($response->getContent());

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('range_too_large', $payload['error']['code'] ?? null);
    }

    public function testInvokeReturnsBadRequestWhenRangeStartsBeforeSupportedYear(): void
    {
        $controller = $this->createController();

        $response = $controller(
            new Request(['from' => '1999-12-31', 'to' => '2000-01-10', 'budget' => '3', 'strategy' => 'best_ratio'])
        );
        $payload = $this->decodeJsonResponse($response->getContent());

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('unsupported_year', $payload['error']['code'] ?? null);
    }

    private function createController(): LeaveRecommendationsController
    {
        return new LeaveRecommendationsController(new LeaveRecommendationEngine(new PolishHolidayProvider()));
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
