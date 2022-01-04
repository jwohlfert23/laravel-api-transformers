<?php

namespace Jwohlfert23\LaravelApiTransformers;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
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
        $key = config('api-transformers.request_include_key', 'include');

        return $this->parseRelationships(request($key, ''));
    }

    public function item($toTransform, BaseTransformer $transformer = null): array|null
    {
        if (! $transformer) {
            $transformer = new DefaultTransformer();
        }
        $transformer->setIncludeFromRequest($this->getIncludesFromRequest());

        if ($toTransform instanceof Builder) {
            $transformer->processQuery($toTransform);
            $item = $toTransform->first();
        } else {
            if ($toTransform instanceof Model) {
                $transformer->loadMissing($toTransform);
            }
            $item = $toTransform;
        }

        if (! $item) {
            return null;
        }

        return $transformer->process($item);
    }

    public function collection($toTransform, BaseTransformer $transformer = null): array
    {
        if (! $transformer) {
            $transformer = new DefaultTransformer();
        }
        $transformer->setIncludeFromRequest($this->getIncludesFromRequest());

        if ($toTransform instanceof Builder) {
            $transformer->processQuery($toTransform);
            $collection = $toTransform->get();
        } else {
            if ($toTransform instanceof Collection) {
                $transformer->loadMissing($toTransform);
            }
            $collection = $toTransform;
        }

        return [
            'data' => $collection->map(fn ($item) => $transformer->process($item))->toArray(),
        ];
    }

    public function paginate(Builder $builder, int $count, BaseTransformer $transformer = null)
    {
        if (! $transformer) {
            $transformer = new DefaultTransformer();
        }
        $transformer
            ->setIncludeFromRequest($this->getIncludesFromRequest())
            ->processQuery($builder);

        return $builder->paginate($count)->through(fn ($item) => $transformer->process($item))->toArray();
    }

    public function paginator(Paginator $paginator, BaseTransformer $transformer = null)
    {
        if (! $transformer) {
            $transformer = new DefaultTransformer();
        }
    }
}
