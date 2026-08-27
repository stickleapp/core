<?php

declare(strict_types=1);

use StickleApp\Core\Models\Segment as SegmentModel;

/**
 * Segment::objects() reads the configured prefix to build the pivot name and
 * to strip the join belongsToMany() generated, then adds its replacement join
 * against a hard-coded 'stc_model_segment'. Under the default prefix the two
 * names coincide and nothing shows; under any other prefix the relation joins
 * a table the application does not write to.
 *
 * The prefix is restored in afterEach rather than at the end of the test body:
 * migrations run per test and roll back in teardown using this config, so a
 * failed assertion that skipped the restore would leave the schema behind and
 * break every subsequent test.
 */
beforeEach(function (): void {
    $this->originalPrefix = config('stickle.database.tablePrefix');
});

afterEach(function (): void {
    config(['stickle.database.tablePrefix' => $this->originalPrefix]);
});

test('objects() joins the pivot table under the configured prefix', function (): void {
    $segment = SegmentModel::query()->create([
        'name' => 'Prefixed',
        'model_class' => 'User',
        'as_class' => 'Prefixed',
        'description' => 'p',
    ]);

    config(['stickle.database.tablePrefix' => 'acme_']);

    $sql = $segment->objects()->toSql();

    expect($sql)->toContain('acme_model_segment')
        ->and($sql)->not->toContain('stc_model_segment');
});
