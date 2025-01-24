<?php

use StickleApp\Core\Providers\CoreServiceProvider;

it('can be instantiated', function () {
    $obj = new CoreServiceProvider(app());
    expect($obj)->toBeInstanceOf(CoreServiceProvider::class);
});
