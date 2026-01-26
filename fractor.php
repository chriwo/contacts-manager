<?php

declare(strict_types=1);

use a9f\Fractor\Configuration\FractorConfiguration;
use a9f\Typo3Fractor\Set\Typo3LevelSetList;

return FractorConfiguration::configure()
    ->withPaths([
        __DIR__ . '/',
    ])
    ->withSets([
        Typo3LevelSetList::UP_TO_TYPO3_13,
    ])
    ->withSkip([
        // Skip paths
        __DIR__ . '/.build',
        __DIR__ . '/.ddev',
        __DIR__ . '/.github',
        __DIR__ . '/.idea',
        __DIR__ . '/config',
        __DIR__ . '/var',
    ]);
