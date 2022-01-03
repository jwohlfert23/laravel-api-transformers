<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Builder;
use Jwohlfert23\LaravelApiTransformers\BaseTransformer;
use Jwohlfert23\LaravelApiTransformers\Database\Models;

class DefaultTransformer extends BaseTransformer
{
    public function transform($model)
    {
        return $model->toArray();
    }
}

class CustomerTransformer extends BaseTransformer
{
    protected array $defaultIncludes = ['sales_rep'];
    protected array $availableIncludes = ['orders', 'sales_rep'];

    public function transformQuery(Builder $builder)
    {
        $builder->withCount('orders');
    }

    public function transform(Models\Customer $customer)
    {
        $array = $customer->toArray();
        $array['testing'] = 123;
        return $array;
    }

    public function includeSalesRep(Models\Customer $customer)
    {
        return $this->relation($customer, 'salesRep', new DefaultTransformer);
    }

    public function includeOrders(Models\Customer $customer)
    {
        return $this->relation($customer, 'orders', new DefaultTransformer());
    }
}

beforeEach(function () {
    Schema::dropIfExists('users');
    Schema::dropIfExists('customers');
    Schema::dropIfExists('orders');

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    Schema::create('customers', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->foreignId('sales_rep_id')->constrained()->cascadeOnDelete();
        $table->timestamps();
    });

    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
        $table->foreignId('sales_rep_id')->constrained()->cascadeOnDelete();
        $table->string('description');
        $table->timestamps();
    });
});

function api()
{
    return new Jwohlfert23\LaravelApiTransformers\LaravelApiTransformers();
}
//
//it('eager loads from request and defaults', function () {
//    $transformer = new CustomerTransformer();
//
//    $transformer->setIncludeFromRequest(['orders' => true]);
//
//    $builder = Models\Customer::query();
//    $transformer->processQuery($builder);
//
//    expect(array_keys($builder->getEagerLoads()))->toEqual(['salesRep', 'orders']);
//});

it('produces the correct result', function () {
    $user = Models\User::factory()->create();
    $customer = Models\Customer::factory()->withSalesRep($user)->create();
    Models\Order::factory()->count(2)->for($customer)->withSalesRep($user)->create();


   request()->query->set('include', 'orders');
    $res = api()->collection(Models\Customer::query(), new CustomerTransformer());

    expect($res['data'])->toHaveCount(1);


    expect($res['data'][0])
        ->toHaveKey('sales_rep.id', 1)
        ->toHaveKey('testing', 123)
        ->toHaveKey('orders_count', 2)
        ->not->toHaveKey('salesRep');

    expect($res['data'][0]['orders'])->toHaveCount(2);
});
