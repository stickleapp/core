<?php

declare(strict_types=1);

use StickleApp\Core\Support\ClassUtils;

test('directoryFromNamespace converts namespace to filesystem path using composer PSR-4 mappings', function (): void {
    // Test with App namespace (mapped to 'app/' in Laravel's composer.json)
    $result = ClassUtils::directoryFromNamespace('App\\Segments');
    expect($result)->toContain('app'.DIRECTORY_SEPARATOR.'Segments');
    expect($result)->toEndWith('app'.DIRECTORY_SEPARATOR.'Segments');

    // Test with this package's namespace (mapped to 'src/' in composer.json)
    $result = ClassUtils::directoryFromNamespace('StickleApp\\Core\\Support');
    expect($result)->toContain('src'.DIRECTORY_SEPARATOR.'Support');
    expect($result)->toEndWith('src'.DIRECTORY_SEPARATOR.'Support');

    // Test nested namespace under package namespace
    $result = ClassUtils::directoryFromNamespace('StickleApp\\Core\\Support\\Helpers');
    expect($result)->toContain('src'.DIRECTORY_SEPARATOR.'Support'.DIRECTORY_SEPARATOR.'Helpers');
    expect($result)->toEndWith('src'.DIRECTORY_SEPARATOR.'Support'.DIRECTORY_SEPARATOR.'Helpers');

    // Test with Workbench namespace (mapped to 'workbench/app/' in composer.json autoload-dev)
    $result = ClassUtils::directoryFromNamespace('Workbench\\App\\Models');
    expect($result)->toContain('workbench'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Models');
    expect($result)->toEndWith('workbench'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Models');

    // Test fallback for unmapped namespace
    $result = ClassUtils::directoryFromNamespace('UnmappedNamespace\\Models');
    expect($result)->toContain('UnmappedNamespace'.DIRECTORY_SEPARATOR.'Models');
});
