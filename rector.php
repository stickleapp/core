<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorLaravel\Rector\Class_\ModelCastsPropertyToCastsMethodRector;
use RectorLaravel\Set\LaravelLevelSetList;
use RectorLaravel\Set\LaravelSetList;
use RectorLaravel\Set\LaravelSetProvider;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/public',
        __DIR__.'/resources',
        __DIR__.'/routes',
        __DIR__.'/tests',
        __DIR__.'/src',
        __DIR__.'/workbench/app',
        __DIR__.'/workbench/bootstrap',
        // __DIR__.'/workbench/config',
        __DIR__.'/workbench/database',
        // __DIR__ . "/workbench/public",
        __DIR__.'/workbench/resources',
        __DIR__.'/workbench/routes',
    ])
    ->withSkip([
        __DIR__.'/vendor',
        __DIR__.'/node_modules',
        __DIR__.'/storage',
        // Skip ModelCastsPropertyToCastsMethodRector for ModelAttributes.php
        // The $casts property is required for reliable mass assignment operations
        ModelCastsPropertyToCastsMethodRector::class => [
            __DIR__.'/src/Models/ModelAttributes.php',
        ],
    ])
    ->withCache(cacheDirectory: __DIR__.'/storage/rector')
    ->withPhpSets(php83: true)
    ->withSetProviders(LaravelSetProvider::class)
    ->withSets([
        LaravelLevelSetList::UP_TO_LARAVEL_120,
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_IF_HELPERS,
        LaravelSetList::LARAVEL_FACADE_ALIASES_TO_FULL_NAMES,
        LaravelSetList::LARAVEL_COLLECTION,
        LaravelSetList::LARAVEL_ARRAY_STR_FUNCTION_TO_STATIC_CALL,
        LaravelSetList::LARAVEL_ARRAYACCESS_TO_METHOD_CALL,
        LaravelSetList::LARAVEL_CONTAINER_STRING_TO_FULLY_QUALIFIED_NAME,
        LaravelSetList::LARAVEL_TYPE_DECLARATIONS,
        LaravelSetList::LARAVEL_ELOQUENT_MAGIC_METHOD_TO_QUERY_BUILDER,
        LaravelSetList::LARAVEL_FACTORIES,
        LaravelSetList::LARAVEL_LEGACY_FACTORIES_TO_CLASSES,
        LaravelSetList::LARAVEL_TESTING,
        // DO NOT IMPLEMENT:
        // LaravelSetList::LARAVEL_STATIC_TO_INJECTION, Too verbose
    ])
    ->withComposerBased(laravel: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        earlyReturn: true,
        typeDeclarations: true,
        privatization: true,
        naming: true,
        instanceOf: true,
    )
    ->withParallel()
    ->withImportNames();
