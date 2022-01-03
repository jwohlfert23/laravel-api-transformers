<?php

namespace Jwohlfert23\LaravelApiTransformers\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jwohlfert23\LaravelApiTransformers\Database\Models\Order;
use Jwohlfert23\LaravelApiTransformers\Database\Models\User;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'description' => $this->faker->word
        ];
    }

    public function withSalesRep(User $user)
    {
        return $this->state([
            'sales_rep_id' => $user->id
        ]);
    }
}
