<?php

namespace Paulo\FileProcessorServer\Services;

use Paulo\FileProcessorServer\Attributes\Controller;
use Paulo\FileProcessorServer\Attributes\Route;
use ReflectionMethod;

class RouterCacheFactory extends ClassCacheFactory
{
    protected $fileName = 'route.php';
    protected function filterClassesToCache(string $classPath, string $classSting): bool
    {
        if (!str_starts_with($classSting, 'Paulo\\')) {
            return false;
        }
        $class = new \ReflectionClass($classSting);
        if (!$class->getAttributes(Controller::class)) {
            return false;
        }
        return (bool) array_filter($class->getMethods(), function (ReflectionMethod $reflectionMethod) {
            return (bool) $reflectionMethod->getAttributes(Route::class);
        });
    }

    protected function getClassesToCache(): array
    {
        $classes = parent::getClassesToCache();
        $routes = [];
        foreach ($classes as $classSting) {
            $class = new \ReflectionClass($classSting);
            $methods = $class->getMethods();
            foreach ($methods as $method) {
                if ($attributes = $method->getAttributes(Route::class)) {
                    foreach ($attributes as $routeAttribute) {
                        /**
                         * @var  Route $instance
                         */
                        $instance = $routeAttribute->newInstance();

                        $routes[$instance->method][$instance->path] = [
                            'class' => $classSting,
                            'method' => $method->getName(),
                        ];
                    }
                }
            }
        }

        return $routes;
    }
}