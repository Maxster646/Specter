<?php

namespace Specter\Core;

class App
{
    private Router $router;
    private array $middleware = [];

    /**
     * Initialize the application
     */
    public function __construct()
    {
        $this->router = new Router();
    }

    // --------------------
    // Routing Methods
    // --------------------
    public function get(string $path, callable|string $handler): void
    {
        $this->router->add('GET', $path, $handler);
    }

    public function post(string $path, callable|string $handler): void
    {
        $this->router->add('POST', $path, $handler);
    }

    // --------------------
    // Middleware
    // --------------------
    public function middleware(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    // --------------------
    // Route Groups
    // --------------------
    public function group(string $prefix, callable $callback): void
    {
        // Pass App instance to callback for professional handling
        $this->router->group($prefix, function () use ($callback) {
            $callback($this);
        });
    }

    // --------------------
    // Response Helpers
    // --------------------
    public function json(array $data, int $status = 200, array $headers = []): void
    {
        http_response_code($status);
        header('Content-Type: application/json');

        foreach ($headers as $key => $value) {
            header("$key: $value");
        }

        echo json_encode($data);
        exit;
    }

    public function error404(array $data = []): void
    {
        $this->json(array_merge(['error' => 'Not Found'], $data), 404);
    }

    public function error500(array $data = []): void
    {
        $this->json(array_merge(['error' => 'Internal Server Error'], $data), 500);
    }

    // --------------------
    // Run Application
    // --------------------
    public function run(): void
    {
        try {
            // Execute all middleware
            foreach ($this->middleware as $mw) {
                $mw();
            }

            $scriptName = $_SERVER['SCRIPT_NAME'];
            $uri = $_SERVER['REQUEST_URI'];

            $basePath = str_replace('/index.php', '', $scriptName);
            $uri = preg_replace('#^' . preg_quote($basePath) . '#', '', $uri);
            $uri = $uri === '' ? '/' : $uri;

            $response = $this->router->dispatch($_SERVER['REQUEST_METHOD'], $uri);

            if ($response !== null) {
                if (is_array($response)) {
                    $this->json($response);
                } else {
                    echo $response;
                }
            } else {
                $this->error404();
            }
        } catch (\Throwable $e) {
            $this->error500(['message' => $e->getMessage()]);
        }
    }
}
