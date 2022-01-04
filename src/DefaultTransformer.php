<?php

namespace Jwohlfert23\LaravelApiTransformers;

use Illuminate\Contracts\Support\Arrayable;

class DefaultTransformer extends BaseTransformer
{
    public function transform($item)
    {
        if (is_array($item)) {
            return $item;
        }
        if ($item instanceof Arrayable) {
            return $item->toArray();
        }

        return json_decode(json_encode($item), true);
    }
}
