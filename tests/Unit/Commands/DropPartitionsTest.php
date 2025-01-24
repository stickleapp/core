<?php

test('Command Exists', function () {
    $this->artisan("stickle:drop-partitions lc_events_rollup_1day public week '2024-09-01'")->assertExitCode(0);
});
