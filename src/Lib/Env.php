<?php

namespace Paulo\FileProcessorServer\Lib;

class Env
{
    private static Env $self;
    protected array $env = [];
    private function __construct()
    {
        $dotenv = \Dotenv\Dotenv::createImmutable(base_path(''));
        $this->env = $dotenv->load();
        $dotenv->required(['HOST', 'PORT'])->notEmpty();
    }

    public function get(string $key)
    {
        return $this->env[$key] ?? null;
    }

    public static function instance(): Env
    {
        if (isset(static::$self)) {
            return static::$self;
        }

        return static::$self = new Env();
    }
}