<?php

namespace Jwohlfert23\LaravelApiTransformers\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Jwohlfert23\LaravelApiTransformers\LaravelApiTransformers
 */
class LaravelApiTransformers extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-api-transformers';
    }
}
