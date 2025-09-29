<?php

namespace Specter\Core;

class Router
{
    /**
     * Stores all routes organized by HTTP method
     *
     * @var array
     */
    private array $routes = [];

    /**
     * Register a route for a specific HTTP method
     *
     * @param string   $method  HTTP method (GET, POST, etc.)
     * @param string   $path    Route path, supports parameters like /user/{id}
     * @param callable $handler Function to execute when route matches
     */
    public function add(string $method, string $path, callable $handler): void
    {
        $method = strtoupper($method);
        $this->routes[$method][$path] = $handler;
    }

    /**
     * Dispatch the incoming request to the correct route handler
     *
     * @param string $method HTTP method of the request
     * @param string $uri    URI of the request
     * @return mixed|null    Handler response or null if no match
     */
    public function dispatch(string $method, string $uri)
    {
        $method = strtoupper($method);
        $uri = rtrim($uri, '/'); // remove trailing slash

        if (!isset($this->routes[$method])) {
            return null;
        }

        foreach ($this->routes[$method] as $route => $handler) {
            // Convert route parameters {param} into regex capture groups
            $pattern = preg_replace('#\{[\w]+\}#', '([\w-]+)', $route);
            $pattern = "#^" . rtrim($pattern, '/') . "$#";

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // remove full match
                return call_user_func_array($handler, $matches);
            }
        }

        return null; // no route matched
    }
}
