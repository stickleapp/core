<?php

declare(strict_types=1);

test('example', function () {
    expect(true)->toBeTrue();
});

// it('debug config loading', function () {
//     // Check if config file exists
//     $configPath = __DIR__.'/../../config/stickle.php';
//     expect(file_exists($configPath))->toBeTrue();

//     // Check what's actually in config
//     dd(config('stickle')); // This will show you the entire config structure
// array:1 [
//   "broadcasting" => array:1 [
//     "channels" => array:1 [
//       "object" => "stickle.object.%s.%s"
//     ]
//   ]
//     // Check specific keys
//     dump(config('stickle.database.partitions.events.extension'));
// });
