<?php

declare(strict_types=1);

use StickleApp\Core\CoreServiceProvider;

it('can be instantiated', function () {
    $obj = new CoreServiceProvider(app());
    expect($obj)->toBeInstanceOf(CoreServiceProvider::class);
});
