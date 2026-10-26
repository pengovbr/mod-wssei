<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withSkip([
        __DIR__ . '/src/vendor',
        __DIR__ . '/node_modules',
    ])
    ->withSets([
        LevelSetList::UP_TO_PHP_85,
    ])
    // PHP 8.5 feature adoption (typed constants, #[\Override], etc.)
    ->withPhpSets(
        php85: true,
    )
    // Optional: tighten code quality
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
    )
    ->withTypeCoverageLevel(0);
