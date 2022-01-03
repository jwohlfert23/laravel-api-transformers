<?php

namespace Jwohlfert23\LaravelApiTransformers\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jwohlfert23\LaravelApiTransformers\Database\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name
        ];
    }
}
