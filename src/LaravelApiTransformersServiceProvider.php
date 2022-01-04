<?php

namespace Jwohlfert23\LaravelApiTransformers;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Jwohlfert23\LaravelApiTransformers\Commands\LaravelApiTransformersCommand;

class LaravelApiTransformersServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-api-transformers')
            ->hasConfigFile();
    }
}
