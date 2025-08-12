<?php

test('Command Exists', function () {
    $this->artisan("stickle:create-partitions {$this->tablePrefix}requests_rollup_1min public week '2024-08-01'")->assertExitCode(0);
});
