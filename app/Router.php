<?php
namespace App;

class Router
{
    private array $routes = [];
    private string $prefix = '';

    public function get(string $path, callable|array $handler): void
    {
        $this->routes[] = ['GET', $this->prefix . $path, $handler];
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->routes[] = ['POST', $this->prefix . $path, $handler];
    }

    public function any(string $path, callable|array $handler): void
    {
        $this->routes[] = ['ANY', $this->prefix . $path, $handler];
    }

    public function group(string $prefix, callable $callback): void
    {
        $old = $this->prefix;
        $this->prefix = $old . $prefix;
        $callback($this);
        $this->prefix = $old;
    }

    public function dispatch(): void
    {
        $method = requestMethod();
        $path   = '/' . trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        if ($path === '/') $path = '/';

        foreach ($this->routes as [$routeMethod, $routePath, $handler]) {
            if ($routeMethod !== 'ANY' && $routeMethod !== $method) continue;

            $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $routePath);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                if (is_callable($handler)) {
                    call_user_func($handler, ...array_values($params));
                } elseif (is_array($handler)) {
                    [$class, $methodName] = $handler;
                    $controller = new $class();
                    $controller->$methodName(...array_values($params));
                }
                return;
            }
        }

        // 404
        http_response_code(404);
        view('errors.404');
    }
}
