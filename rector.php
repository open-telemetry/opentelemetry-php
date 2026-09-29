<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\ValueObject\PhpVersion;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersion::PHP_83);

    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $rectorConfig->sets([
        SetList::PHP_81,
        SetList::CODE_QUALITY,
        PHPUnitSetList::COMPOSER_BASED,
    ]);
    $rectorConfig->rule(AddOverrideAttributeToOverriddenMethodsRector::class);
    $rectorConfig->skip([
        FlipTypeControlToUseExclusiveTypeRector::class,
        ReadOnlyPropertyRector::class => [
            __DIR__ . '/src/SDK/Metrics/Stream/SynchronousMetricStream.php',
            __DIR__ . '/tests/Unit/Extension/Propagator',
        ],
        LocallyCalledStaticMethodToNonStaticRector::class,
    ]);
};
