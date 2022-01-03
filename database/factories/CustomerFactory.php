<?php

namespace Jwohlfert23\LaravelApiTransformers\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Jwohlfert23\LaravelApiTransformers\Database\Models\Customer;
use Jwohlfert23\LaravelApiTransformers\Database\Models\User;


class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company
        ];
    }

    public function withSalesRep(User $user)
    {
        return $this->state([
            'sales_rep_id' => $user->id
        ]);
    }
}
