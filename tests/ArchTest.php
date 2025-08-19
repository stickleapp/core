<?php

declare(strict_types=1);

arch('it will not use debugging functions')
    ->skip('Debugging PHPUnit issue')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();
