<?php

namespace Specter\Core;

class App
{
    /**
     * @var Router
     */
    private Router $router;

    /**
     * @var array Global middleware callables
     */
    private array $middleware = [];

    /**
     * Constructor: initialize Router
     */
    public function __construct()
    {
        $this->router = new Router();
    }

    /**
     * Register a GET route
     */
    public function get(string $path, callable $handler): void
    {
        $this->router->add('GET', $path, $handler);
    }

    /**
     * Register a POST route
     */
    public function post(string $path, callable $handler): void
    {
        $this->router->add('POST', $path, $handler);
    }

    /**
     * Add global middleware
     */
    public function middleware(callable $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    /**
     * Run the application: execute middleware, dispatch route, send response
     */
    public function run(): void
    {
        try {
            // Execute global middleware
            foreach ($this->middleware as $mw) {
                $mw();
            }

            // Normalize URI to handle subfolder deployments
            $scriptName = $_SERVER['SCRIPT_NAME'];
            $uri = $_SERVER['REQUEST_URI'];

            $basePath = str_replace('/index.php', '', $scriptName);
            $uri = preg_replace('#^' . preg_quote($basePath) . '#', '', $uri);
            $uri = $uri === '' ? '/' : $uri;

            // Dispatch route
            $response = $this->router->dispatch($_SERVER['REQUEST_METHOD'], $uri);

            if ($response !== null) {
                if (is_array($response)) {
                    header('Content-Type: application/json');
                    echo json_encode($response);
                } else {
                    echo $response;
                }
            } else {
                http_response_code(404);
                echo "Specter 404 - Service not found";
            }
        } catch (\Throwable $e) {
            http_response_code(500);
            echo "Specter 500 - Internal server error";
            // Optional: log $e->getMessage() to file or external service
        }
    }
}
