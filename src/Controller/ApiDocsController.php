<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ApiDocsController
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')] private string $projectDir,
        #[Autowire('%kernel.environment%')] private string $appEnv,
    ) {
    }

    #[Route('/docs/openapi.yaml', name: 'docs_openapi_yaml', methods: ['GET'])]
    public function openapiYaml(): Response
    {
        $this->assertDevEnvironment();

        $path = $this->projectDir.'/docs/openapi.yaml';
        $content = @file_get_contents($path);
        if (!is_string($content)) {
            throw new NotFoundHttpException('OpenAPI file not found.');
        }

        return new Response(
            $content,
            Response::HTTP_OK,
            ['Content-Type' => 'application/yaml; charset=UTF-8']
        );
    }

    #[Route('/docs', name: 'docs_swagger_ui', methods: ['GET'])]
    public function swaggerUi(Request $request): Response
    {
        $this->assertDevEnvironment();

        $openapiUrl = htmlspecialchars($this->openapiUrl($request), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $docsNav = $this->docsNavHtml($request, 'swagger');

        $html = <<<HTML
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Urlopomat API docs (Swagger UI)</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
    <style>
      html, body { margin: 0; padding: 0; }
      #swagger-ui { min-height: calc(100vh - 48px); }
      .docs-nav {
        height: 48px;
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 0 16px;
        background: #111827;
        color: #ffffff;
        font-family: Arial, sans-serif;
      }
      .docs-nav a {
        color: #93c5fd;
        text-decoration: none;
      }
      .docs-nav strong {
        color: #ffffff;
      }
    </style>
  </head>
  <body>
    {$docsNav}
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script>
      window.onload = function () {
        SwaggerUIBundle({
          url: '{$openapiUrl}',
          dom_id: '#swagger-ui',
          deepLinking: true,
          displayRequestDuration: true
        });
      };
    </script>
  </body>
</html>
HTML;

        return new Response(
            $html,
            Response::HTTP_OK,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }

    #[Route('/docs/redoc', name: 'docs_redoc', methods: ['GET'])]
    public function redoc(Request $request): Response
    {
        $this->assertDevEnvironment();

        $openapiUrl = htmlspecialchars($this->openapiUrl($request), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $docsNav = $this->docsNavHtml($request, 'redoc');

        $html = <<<HTML
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Urlopomat API docs (Redoc)</title>
    <style>
      body { margin: 0; padding: 0; }
      .docs-nav {
        height: 48px;
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 0 16px;
        background: #111827;
        color: #ffffff;
        font-family: Arial, sans-serif;
      }
      .docs-nav a {
        color: #93c5fd;
        text-decoration: none;
      }
      .docs-nav strong {
        color: #ffffff;
      }
      redoc {
        display: block;
        height: calc(100vh - 48px);
        overflow: auto;
      }
    </style>
  </head>
  <body>
    {$docsNav}
    <redoc spec-url="{$openapiUrl}"></redoc>
    <script src="https://cdn.redoc.ly/redoc/latest/bundles/redoc.standalone.js"></script>
  </body>
</html>
HTML;

        return new Response(
            $html,
            Response::HTTP_OK,
            ['Content-Type' => 'text/html; charset=UTF-8']
        );
    }

    private function openapiUrl(Request $request): string
    {
        return $request->getSchemeAndHttpHost().$request->getBasePath().'/docs/openapi.yaml';
    }

    private function docsNavHtml(Request $request, string $activeView): string
    {
        $baseUrl = $request->getSchemeAndHttpHost().$request->getBasePath();
        $swaggerUrl = htmlspecialchars($baseUrl.'/docs', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $redocUrl = htmlspecialchars($baseUrl.'/docs/redoc', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $openapiUrl = htmlspecialchars($baseUrl.'/docs/openapi.yaml', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $swaggerLabel = $activeView === 'swagger' ? '<strong>Swagger UI</strong>' : '<a href="'.$swaggerUrl.'">Swagger UI</a>';
        $redocLabel = $activeView === 'redoc' ? '<strong>Redoc</strong>' : '<a href="'.$redocUrl.'">Redoc</a>';

        return '<nav class="docs-nav"><span>'.$swaggerLabel.'</span><span>'.$redocLabel.'</span><a href="'.$openapiUrl.'">openapi.yaml</a></nav>';
    }

    private function assertDevEnvironment(): void
    {
        if ($this->appEnv !== 'dev') {
            throw new NotFoundHttpException();
        }
    }
}
