<?php
namespace App\Core;

/**
 * Regex router with named parameters and per-route middleware.
 *
 *   $router->get('/product/{slug}', [ProductController::class, 'show']);
 *   $router->post('/admin/products', [ProductController::class, 'store'], ['admin']);
 */
class Router
{
    private array $routes = [];

    public function get(string $path, array $action, array $middleware = []): void
    {
        $this->add('GET', $path, $action, $middleware);
    }

    public function post(string $path, array $action, array $middleware = []): void
    {
        $this->add('POST', $path, $action, $middleware);
    }

    private function add(string $method, string $path, array $action, array $middleware): void
    {
        // {slug} -> named capture group. Numeric ids are constrained to digits.
        $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path);
        $this->routes[] = [
            'method'     => $method,
            'pattern'    => '#^' . $pattern . '$#',
            'action'     => $action,
            'middleware' => $middleware,
        ];
    }

    public function dispatch(Request $request): void
    {
        $uri    = $request->path();
        $method = $request->method();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (!preg_match($route['pattern'], $uri, $matches)) {
                continue;
            }

            foreach ($route['middleware'] as $name) {
                Middleware::handle($name);
            }

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            [$class, $method] = $route['action'];
            (new $class())->$method($request, ...array_values($params));
            return;
        }

        http_response_code(404);
        View::render('errors/404', ['title' => 'Page not found']);
    }
}
