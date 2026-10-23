<?php

namespace App\Core;

class Router
{
    private static array $routes = [];
    private static string $basePath;

    public static function init(): void
    {
        self::$basePath = dirname($_SERVER['SCRIPT_NAME']);
        if (str_ends_with(self::$basePath, '/public')) {
            self::$basePath = substr(self::$basePath, 0, -strlen('/public'));
        }
        if (self::$basePath === '/' || self::$basePath === '\\') {
            self::$basePath = '';
        }
    }

    public static function get(string $path, mixed $handler): void
    {
        self::$routes['GET'][$path] = $handler;
    }

    public static function post(string $path, mixed $handler): void
    {
        self::$routes['POST'][$path] = $handler;
    }

    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path from request URI
        if (self::$basePath !== '' && strpos($requestUri, self::$basePath) === 0) {
            $requestUri = substr($requestUri, strlen(self::$basePath));
        }

        if (strpos($requestUri, '/public/') === 0) {
            $requestUri = substr($requestUri, strlen('/public'));
        }
        
        $path = $requestUri === '' ? '/' : $requestUri;

        if (isset(self::$routes[$method][$path])) {
            self::callHandler(self::$routes[$method][$path], []);
            return;
        }

        foreach (self::$routes[$method] as $routePath => $handler) {
            $pattern = preg_replace('#\(\d+\)#', '(\d+)', $routePath);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                self::callHandler($handler, $matches);
                return;
            }
        }

        http_response_code(404);
        echo 'Página não encontrada';
    }

    private static function callHandler(mixed $handler, array $params = []): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
        } elseif (is_string($handler)) {
            list($controller, $action) = explode('@', $handler);
            $controller = 'App\\Controllers\\' . $controller;
            $controller = new $controller();
            call_user_func_array([$controller, $action], $params);
        }
    }

    public static function baseUrl(string $path = ''): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . '://' . $host . self::$basePath . $path;
    }
}
