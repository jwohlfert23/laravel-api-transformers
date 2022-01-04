<?php

namespace Jwohlfert23\LaravelApiTransformers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class BaseTransformer
{
    protected array $availableIncludes = [];
    protected array $defaultIncludes = [];
    protected array $includeFromRequest = [];
    protected ?string $isIncluding = null;

    public function setIncludeFromRequest(array $relations): self
    {
        $this->includeFromRequest = $relations;
        return $this;
    }

    protected function maybeSetIncludeFromRequest(BaseTransformer $transformer)
    {
        if ($this->isIncluding) {
            $children = $this->includeFromRequest[$this->isIncluding] ?? null;
            if (is_array($children)) {
                $transformer->setIncludeFromRequest($children);
            }
        }
    }

    public function relation(Model $model, string $relation, BaseTransformer $transformer = null)
    {
        if (! $transformer) {
            $transformer = new DefaultTransformer();
        }
        return new RelationInclude($model, $relation, $transformer);
    }

    public function item($item, BaseTransformer $transformer = null)
    {
        if (! $transformer) {
            $transformer = new DefaultTransformer();
        }
        $this->maybeSetIncludeFromRequest($transformer);
        return $transformer->process($item);
    }

    public function collection(Collection $items, BaseTransformer $transformer = null)
    {
        if (! $transformer) {
            $transformer = new DefaultTransformer();
        }
        $this->maybeSetIncludeFromRequest($transformer);
        return $items->map(fn($item) => $transformer->process($item))->all();
    }

    protected function getIncludes(): array
    {
        $fromRequest = array_keys(Arr::only($this->includeFromRequest, $this->availableIncludes));
        return array_merge($this->defaultIncludes, $fromRequest);
    }

    protected function getMethodForInclude(string $include): string
    {
        return 'include'.Str::studly($include);
    }

    protected function getToEagerLoad(Model $model): array
    {
        $array = [];
        foreach ($this->getIncludes() as $include) {
            $this->isIncluding = $include;
            $res = $this->{$this->getMethodForInclude($include)}($model);
            if ($res instanceof RelationInclude) {
                $array[$res->getRelationName()] = function ($q) use ($res) {
                    $res->getTransformer()->processQuery($q->getQuery());
                };
            }
        }
        return $array;
    }

    public function processQuery(Builder $builder)
    {
        if (method_exists($this, 'transformQuery')) {
            $this->transformQuery($builder);
        }
        $builder->with($this->getToEagerLoad($builder->getModel()));
    }

    public function loadMissing(Model|EloquentCollection $model): void
    {
        if ($model instanceof EloquentCollection) {
            $first = $model->first();
            if (! $first) {
                return;
            }
        }
        $model->loadMissing($this->getToEagerLoad($first ?? $model));
    }

    public function process($model): array
    {
        $array = $this->transform($model);

        foreach ($this->getIncludes() as $include) {
            $this->isIncluding = $include;

            $res = $this->{$this->getMethodForInclude($include)}($model);

            if ($res instanceof RelationInclude) {
                $results = $model->{$res->getRelationName()};
                $method = $results instanceof Collection ? 'collection' : 'item';
                $array[$include] = $this->{$method}($results, $res->getTransformer());
            } else {
                $array[$include] = $res;
            }
            $this->isIncluding = null;
        }

        return $array;
    }
}
