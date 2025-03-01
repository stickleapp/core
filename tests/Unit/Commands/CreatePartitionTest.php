<?php

test('Command Exists', function () {
    $this->artisan("stickle:create-partitions {$this->tablePrefix}events_rollup_1min public week '2024-08-01'")->assertExitCode(0);
});
