<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\LeaveAnalyzeController;
use App\Domain\Holiday\PolishHolidayProvider;
use App\Domain\Leave\LeaveAnalyzer;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

final class LeaveAnalyzeControllerTest extends TestCase
{
    public function testInvokeReturnsAnalysisForValidPayload(): void
    {
        $controller = $this->createController();

        $response = $controller(
            Request::create(
                '/api/v1/leave/analyze',
                'POST',
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode(['from' => '2026-01-01', 'to' => '2026-01-10'], JSON_THROW_ON_ERROR)
            )
        );
        $payload = $this->decodeJsonResponse($response->getContent());

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('PL', $payload['country'] ?? null);
        self::assertSame(5, $payload['leave_days_required'] ?? null);
        self::assertSame(5, $payload['non_workdays'] ?? null);
    }

    public function testInvokeReturnsBadRequestWhenJsonIsInvalid(): void
    {
        $controller = $this->createController();

        $response = $controller(
            Request::create(
                '/api/v1/leave/analyze',
                'POST',
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                '{"from":"2026-01-01",'
            )
        );
        $payload = $this->decodeJsonResponse($response->getContent());

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('invalid_json', $payload['error']['code'] ?? null);
    }

    public function testInvokeReturnsBadRequestWhenFromIsMissing(): void
    {
        $controller = $this->createController();

        $response = $controller($this->jsonRequest(['to' => '2026-01-10']));
        $payload = $this->decodeJsonResponse($response->getContent());

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('missing_from', $payload['error']['code'] ?? null);
    }

    public function testInvokeReturnsBadRequestWhenDateFormatIsInvalid(): void
    {
        $controller = $this->createController();

        $response = $controller($this->jsonRequest(['from' => '2026/01/01', 'to' => '2026-01-10']));
        $payload = $this->decodeJsonResponse($response->getContent());

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('invalid_from', $payload['error']['code'] ?? null);
    }

    public function testInvokeReturnsBadRequestWhenRangeIsTooLarge(): void
    {
        $controller = $this->createController();

        $response = $controller($this->jsonRequest(['from' => '2026-01-01', 'to' => '2027-01-02']));
        $payload = $this->decodeJsonResponse($response->getContent());

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('range_too_large', $payload['error']['code'] ?? null);
    }

    public function testInvokeReturnsBadRequestWhenDateBeforeSupportedYear(): void
    {
        $controller = $this->createController();

        $response = $controller($this->jsonRequest(['from' => '1999-12-31', 'to' => '2000-01-03']));
        $payload = $this->decodeJsonResponse($response->getContent());

        self::assertSame(400, $response->getStatusCode());
        self::assertSame('unsupported_year', $payload['error']['code'] ?? null);
    }

    private function createController(): LeaveAnalyzeController
    {
        return new LeaveAnalyzeController(new LeaveAnalyzer(new PolishHolidayProvider()));
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function jsonRequest(array $payload): Request
    {
        return Request::create(
            '/api/v1/leave/analyze',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
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
