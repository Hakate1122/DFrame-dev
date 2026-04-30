<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/app',
        __DIR__ . '/public',
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_80, // Set PHP 8.0 as the target version for refactoring

        SetList::CODE_QUALITY, // Set of rules to improve code quality

        SetList::DEAD_CODE, // Set of rules to remove dead code

        SetList::EARLY_RETURN, // Set of rules to refactor code to use early returns
    ]);
};