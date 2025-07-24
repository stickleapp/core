<?php

declare(strict_types=1);

use StickleApp\Core\Support\ClassUtils;

test('directoryFromNamespace converts namespace to filesystem path', function () {
    // Test basic namespace conversion
    $result = ClassUtils::directoryFromNamespace('App\\Segments');
    expect($result)->toContain('App' . DIRECTORY_SEPARATOR . 'Segments');
    expect($result)->toEndWith('App' . DIRECTORY_SEPARATOR . 'Segments');
    
    // Test nested namespace conversion
    $result = ClassUtils::directoryFromNamespace('MyProject\\Controllers\\Api');
    expect($result)->toContain('MyProject' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'Api');
    expect($result)->toEndWith('MyProject' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'Api');
    
    // Test single level namespace
    $result = ClassUtils::directoryFromNamespace('Models');
    expect($result)->toEndWith('Models');
});