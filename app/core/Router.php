<?php

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';
        $base = rtrim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');

        // URL normal: /sistem-parkir/customers
        if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) {
            $path = substr($path, strlen($base));
        }

        // Kompatibilitas jika user membuka URL lama: /sistem-parkir/public/...
        if ($path === '/public' || str_starts_with($path, '/public/')) {
            $path = substr($path, strlen('/public')) ?: '/';
        }

        $path = $this->normalize($path);
        $route = $this->routes[$method][$path] ?? null;

        if (!$route) {
            http_response_code(404);
            require __DIR__ . '/../views/errors/404.php';
            return;
        }

        // Semua request POST wajib membawa token CSRF yang valid.
        if ($method === 'POST' && !verifyCsrfToken()) {
            http_response_code(419);
            $title = 'Sesi Kedaluwarsa';
            require __DIR__ . '/../views/errors/419.php';
            return;
        }

        // Pembatasan akses dilakukan di backend, bukan hanya menyembunyikan menu.
        if (function_exists('authorizeRoute')) {
            authorizeRoute($method, $path);
        }

        [$controller, $action] = $route;
        $controllerFile = __DIR__ . '/../controllers/' . $controller . '.php';

        if (!is_file($controllerFile)) {
            http_response_code(500);
            exit('Controller tidak ditemukan.');
        }

        require_once $controllerFile;
        $instance = new $controller();

        if (!method_exists($instance, $action)) {
            http_response_code(500);
            exit('Method controller tidak ditemukan.');
        }

        $instance->$action();
    }

    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '//' ? '/' : $path;
    }
}
