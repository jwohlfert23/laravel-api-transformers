<?php

namespace Jwohlfert23\LaravelApiTransformers\Commands;

use Illuminate\Console\Command;

class LaravelApiTransformersCommand extends Command
{
    public $signature = 'laravel-api-transformers';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
