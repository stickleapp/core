<?php

test('Command Exists', function () {
    $this->artisan('cascade:create-partition')->assertExitCode(0);
});
