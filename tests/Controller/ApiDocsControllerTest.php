<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\ApiDocsController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ApiDocsControllerTest extends TestCase
{
    public function testOpenapiYamlReturnsSpecificationInDev(): void
    {
        $controller = $this->createController('dev');

        $response = $controller->openapiYaml();
        $content = $response->getContent();

        self::assertSame(200, $response->getStatusCode());
        self::assertIsString($content);
        self::assertStringContainsString('openapi: 3.0.3', $content);
        self::assertStringContainsString('application/yaml', (string) $response->headers->get('Content-Type'));
    }

    public function testSwaggerUiReturnsHtmlPageInDev(): void
    {
        $controller = $this->createController('dev');
        $request = Request::create('https://example.test/docs', 'GET');

        $response = $controller->swaggerUi($request);
        $content = $response->getContent();

        self::assertSame(200, $response->getStatusCode());
        self::assertIsString($content);
        self::assertStringContainsString('SwaggerUIBundle', $content);
        self::assertStringContainsString('https://example.test/docs/openapi.yaml', $content);
        self::assertStringContainsString('https://example.test/docs/redoc', $content);
        self::assertStringContainsString('<strong>Swagger UI</strong>', $content);
    }

    public function testRedocReturnsHtmlPageInDev(): void
    {
        $controller = $this->createController('dev');
        $request = Request::create('https://example.test/docs/redoc', 'GET');

        $response = $controller->redoc($request);
        $content = $response->getContent();

        self::assertSame(200, $response->getStatusCode());
        self::assertIsString($content);
        self::assertStringContainsString('<redoc spec-url=', $content);
        self::assertStringContainsString('https://example.test/docs/openapi.yaml', $content);
        self::assertStringContainsString('https://example.test/docs', $content);
        self::assertStringContainsString('<strong>Redoc</strong>', $content);
    }

    public function testDocsAreHiddenOutsideDevEnvironment(): void
    {
        $controller = $this->createController('prod');

        $this->expectException(NotFoundHttpException::class);
        $controller->openapiYaml();
    }

    private function createController(string $appEnv): ApiDocsController
    {
        return new ApiDocsController(dirname(__DIR__, 2), $appEnv);
    }
}
