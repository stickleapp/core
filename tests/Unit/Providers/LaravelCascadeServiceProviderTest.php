<?php

use Dclaysmith\LaravelCascade\Providers\LaravelCascadeServiceProvider;

it('can be instantiated', function () {
    $obj = new LaravelCascadeServiceProvider(app());
    expect($obj)->toBeInstanceOf(LaravelCascadeServiceProvider::class);
});
