<?php

namespace Specter\Core;

class Router
{
    /**
     * Array to store all routes by method
     * @var array
     */
    private array $routes = [];

    /**
     * Current group prefix for route grouping
     * @var string
     */
    private string $currentGroupPrefix = '';

    /**
     * Add a route to the router
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $path Route path
     * @param callable|string $handler Closure or controller@method
     */
    public function add(string $method, string $path, callable|string $handler): void
    {
        $path = $this->currentGroupPrefix . $path;
        $this->routes[$method][$path] = $handler;
    }

    /**
     * Create a group of routes with a shared prefix
     *
     * @param string $prefix Route prefix
     * @param callable $callback Function that defines grouped routes
     */
    public function group(string $prefix, callable $callback): void
    {
        $previousPrefix = $this->currentGroupPrefix;
        $this->currentGroupPrefix .= $prefix;
        $callback($this); // Pass Router instance to callback
        $this->currentGroupPrefix = $previousPrefix;
    }

    /**
     * Dispatch a request to the matching route
     *
     * @param string $method HTTP method
     * @param string $uri Request URI
     * @return mixed|null Response or null if not found
     */
    public function dispatch(string $method, string $uri)
    {
        if (!isset($this->routes[$method])) {
            return null;
        }

        foreach ($this->routes[$method] as $path => $handler) {
            $pattern = preg_replace('#\{[\w]+\}#', '([\w-]+)', $path);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match

                // If handler is controller@method string, call the method
                if (is_string($handler) && strpos($handler, '@') !== false) {
                    [$controller, $methodName] = explode('@', $handler);
                    $controllerClass = "App\\Controllers\\$controller";
                    if (class_exists($controllerClass)) {
                        $obj = new $controllerClass();
                        return $obj->$methodName(...$matches);
                    }
                }

                // Otherwise, call closure
                if (is_callable($handler)) {
                    return $handler(...$matches);
                }
            }
        }

        return null;
    }
}
