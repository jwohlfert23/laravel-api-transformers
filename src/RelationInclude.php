<?php

namespace Jwohlfert23\LaravelApiTransformers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class RelationInclude
{
    protected Relation $relation;

    public function __construct(
        protected Model $model,
        protected string $relationName,
        protected BaseTransformer $transformer
    ) {
        $this->relation = $model->{$relationName}();
    }

    public function getRelation(): Relation
    {
        return $this->relation;
    }

    public function getRelationName(): string
    {
        return $this->relationName;
    }

    public function getTransformer(): BaseTransformer
    {
        return $this->transformer;
    }
}
