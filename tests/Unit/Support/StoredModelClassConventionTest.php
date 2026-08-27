<?php

declare(strict_types=1);

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * The schema stores model_class as a basename. Writing the fully qualified name
 * into a query that binds that column matches nothing, and because the reader
 * usually falls back -- a left join yields NULL, a where yields an empty set --
 * the failure is silent. That mistake has now been made four times:
 * scopeStickleWhere, scopeStickleOrWhere, RecordModelRelationshipStatisticAction
 * and RecordModelAttributesCommand.
 *
 * ClassUtils::storeModelClass() is the one way to spell it. This asserts that
 * every statement binding the column goes through it.
 *
 * The rule looks only at a QUOTED occurrence of the column, so it covers query
 * bindings and ignores the ModelDto `model_class:` named argument, which is
 * deliberately the runtime class -- see IngestController::getModelDto() and
 * RequestLogger::terminate(), which agree on that.
 */
test('every query binding of model_class goes through storeModelClass', function (): void {
    $offenders = [];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(dirname(__DIR__, 3).'/src')
    );

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $lines = file($file->getPathname()) ?: [];

        foreach ($lines as $number => $line) {
            if (preg_match('/[\'"][^\'"\n]*model_class[\'"]/', $line) !== 1) {
                continue;
            }

            // The binding and its value are often on separate lines, as in a
            // multi-line $join->where(...). Look forward only: a ::class on an
            // earlier line belongs to an earlier statement.
            $window = implode('', array_slice($lines, $number, 4));

            if (str_contains($window, 'storeModelClass')) {
                continue;
            }

            // $model::class is the shape of the mistake. A bare Name::class is
            // usually an exception or enum constant belonging to a neighbouring
            // call, so that only counts on the binding line itself.
            $variableClass = preg_match('/\\$[A-Za-z_][A-Za-z0-9_]*::class/', $window) === 1;
            $literalClass = preg_match('/[A-Za-z_][A-Za-z0-9_]*::class/', $line) === 1;

            if (! $variableClass && ! $literalClass) {
                continue;
            }

            $offenders[] = str_replace(dirname(__DIR__, 3).'/', '', $file->getPathname()).':'.($number + 1);
        }
    }

    expect(array_unique($offenders))->toBe([]);
});
