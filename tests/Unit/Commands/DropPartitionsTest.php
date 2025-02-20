<?php

test('Command Exists', function () {
    $this->artisan("stickle:drop-partitions {$this->prefix}rollup_1day public week '2024-09-01'")->assertExitCode(0);
});
