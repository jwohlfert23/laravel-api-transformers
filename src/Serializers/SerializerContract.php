<?php namespace Jwohlfert23\LaravelApiTransformers\Serializers;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

interface SerializerContract
{
    public function item($data): array;

    public function collection(Collection $collection): array;

    public function paginator(Paginator $paginator): array;
}
