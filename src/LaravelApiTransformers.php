<?php

namespace Jwohlfert23\LaravelApiTransformers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class LaravelApiTransformers
{

    public static function parseRelationships($dot)
    {
        $arr = [];
        foreach (array_filter(explode(',', $dot)) as $relation) {
            Arr::set($arr, $relation, true);
        }
        return $arr;
    }

    protected function getIncludesFromRequest()
    {
        return $this->parseRelationships(request('include', ''));
    }

    public function item(Builder $builder, BaseTransformer $transformer): array|null
    {
        $transformer->processQuery($builder);
        $item = $builder->first();

        if (! $item) {
            return null;
        }

        return $transformer
            ->setIncludeFromRequest($this->getIncludesFromRequest())
            ->process($item);
    }

    public function collection(Builder $builder, BaseTransformer $transformer): array
    {
        $transformer
            ->setIncludeFromRequest($this->getIncludesFromRequest())
            ->processQuery($builder);

        return [
            'data' => $builder->get()->map(fn($item) => $transformer->process($item))->toArray()
        ];
    }

    public function paginate(Builder $builder, int $count, BaseTransformer $transformer)
    {
        return $builder->paginate($count)->through(fn($item) => $transformer->process($item))->toArray();
    }
}
