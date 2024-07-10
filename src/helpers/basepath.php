<?php

if (!function_exists('base_path')) {

    function base_path($str)
    {
        $base = __DIR__ . "/../../";
        if (str_starts_with($str, '/')) {
            $str = substr($str, 1);
        }

        return "$base$str";
    }
}