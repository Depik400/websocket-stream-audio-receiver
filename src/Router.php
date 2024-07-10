<?php
use Paulo\FileProcessorServer\Services\RouterCacheFactory;
use Workerman\Protocols\Http\Request;

class Router
{
    public array $routes = [];
    public function __construct()
    {
        $this->routes = RouterCacheFactory::getCache();
    }

    public function resolve(Request $request): false|\Closure
    {
        $method = trim(strtoupper($request->method()));
        $uri = $request->uri();
        $branch = $this->routes[$method] ?? [];
        if (str_ends_with($uri, '/')) {
            $uri = substr($uri, 0, -1);
        }
        if (str_starts_with($uri, '/')) {
            $uri = substr($uri, 1);
        }
        if (!$branch) {
            return false;
        }

        foreach ($branch as $key => ['class' => $value, 'method' => $method]) {
            if (fnmatch($uri, $key)) {
                return (new $value)->{$method}(...);
            }
        }
        return false;
    }
}