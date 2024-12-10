<?php

test('Command Exists', function () {
    $this->artisan("cascade:create-partitions lc_events_rollup_1min public week '2024-08-01'")->assertExitCode(0);
});
