<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Jwohlfert23\LaravelApiTransformers\BaseTransformer;
use Jwohlfert23\LaravelApiTransformers\Database\Models;
use Jwohlfert23\LaravelApiTransformers\DefaultTransformer;
use Mattiasgeniar\PhpunitQueryCountAssertions\AssertsQueryCounts;

class OrderTransformer extends BaseTransformer
{
    protected array $defaultIncludes = ['sales_rep', 'fixed_data'];

    public function transformQuery($query)
    {
        $query->select('orders.*')->addSelect('2 as jack');
    }

    public function transform($model)
    {
        return $model->toArray();
    }

    public function includeFixedData($order)
    {
        return $this->item(['id' => $order->id]);
    }

    public function includeSalesRep($order)
    {
        return $this->relation($order, 'salesRep', new DefaultTransformer());
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
        return $this->relation($customer, 'salesRep');
    }

    public function includeOrders(Models\Customer $customer)
    {
        return $this->relation($customer, 'orders', new OrderTransformer());
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

    $user = Models\User::factory()->create();
    $otherUser = Models\User::factory()->create();
    $customer = Models\Customer::factory()->withSalesRep($user)->create();
    Models\Order::factory()->count(2)->for($customer)->withSalesRep($user)->create();
    Models\Order::factory()->count(2)->for($customer)->withSalesRep($otherUser)->create();
    AssertsQueryCounts::trackQueries();
});

function api()
{
    return new Jwohlfert23\LaravelApiTransformers\LaravelApiTransformers();
}

//
it('eager loads from request and defaults', function () {
    $transformer = new CustomerTransformer();

    $transformer->setIncludeFromRequest(['orders' => true]);

    $builder = Models\Customer::query();
    $transformer->processQuery($builder);

    expect(array_keys($builder->getEagerLoads()))->toEqual(['salesRep', 'orders']);
});


it('uses default includes', function () {
    $res = api()->collection(Models\Customer::query(), new CustomerTransformer());


    expect($res['data'][0])
        ->toHaveKey('sales_rep.id', 1) // uses default
        ->not->toHaveKey('salesRep');

    $this->assertQueryCountMatches(2);
});

it('uses includes from request', function () {
    request()->query->set('include', 'orders');
    $res = api()->collection(Models\Customer::query(), new CustomerTransformer());

    expect($res['data'][0])
        ->toHaveKey('sales_rep.id', 1) // uses default
        ->toHaveKey('orders');

    expect($res['data'][0]['orders'])->toHaveCount(4);
    expect($res['data'][0]['orders'][0])
        ->toHaveKey('jack', 2)
        ->toHaveKey('id')
        ->toHaveKey('fixed_data');

    $this->assertQueryCountMatches(4);
});


it('calls transform + transformQuery functions', function () {
    $res = api()->collection(Models\Customer::query(), new CustomerTransformer());

    expect($res['data'][0])
        ->toHaveKey('testing', 123)
        ->toHaveKey('orders_count', 4);

    $this->assertQueryCountMatches(2);
});


it('handles a collection', function () {
    $res = api()->collection(Models\Customer::all(), new CustomerTransformer());

    expect($res['data'][0])
        ->toHaveKey('sales_rep.id', 1) // uses default
        ->toHaveKey('testing', 123);

    $this->assertQueryCountMatches(2);
});

it('handles an item', function () {
    $res = api()->item(Models\Customer::first(), new CustomerTransformer());

    expect($res)
        ->toHaveKey('sales_rep.id', 1) // uses default
        ->toHaveKey('testing', 123);

    $this->assertQueryCountMatches(2);
});


it('handles an item query', function () {
    $res = api()->item(Models\Customer::query(), new CustomerTransformer());

    expect($res)
        ->toHaveKey('sales_rep.id', 1) // uses default
        ->toHaveKey('testing', 123);

    $this->assertQueryCountMatches(2);
});

it('handles a paginator', function () {
    $res = api()->paginate(Models\Customer::query(), 25, new CustomerTransformer());

    expect($res)
        ->toHaveKey('sales_rep.id', 1) // uses default
        ->toHaveKey('testing', 123);

    $this->assertQueryCountMatches(2);
});
