<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\HealthController;
use PHPUnit\Framework\TestCase;

final class HealthControllerTest extends TestCase
{
    public function testInvokeReturnsExpectedJsonPayload(): void
    {
        $controller = new HealthController();

        $response = $controller();
        $content = $response->getContent();

        self::assertSame(200, $response->getStatusCode());
        self::assertIsString($content);
        self::assertSame(
            ['status' => 'ok'],
            json_decode($content, true, 512, JSON_THROW_ON_ERROR)
        );
    }
}
