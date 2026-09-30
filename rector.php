<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\BooleanAnd\RepeatedAndNotEqualToNotInArrayRector;
use Rector\CodeQuality\Rector\BooleanNot\NegatedAndsToPositiveOrsRector;
use Rector\CodeQuality\Rector\BooleanOr\RepeatedOrEqualToInArrayRector;
use Rector\CodeQuality\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\If_\ObjectExplicitBoolCompareRector;
use Rector\CodingStyle\Rector\FuncCall\FunctionFirstClassCallableRector;
use Rector\Config\RectorConfig;
use Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use Rector\EarlyReturn\Rector\StmtsAwareInterface\ReturnEarlyIfVariableRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\PHPUnit\PHPUnit100\Rector\Class_\ParentTestClassConstructorRector;
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
        ObjectExplicitBoolCompareRector::class,
        RepeatedOrEqualToInArrayRector::class,
        RepeatedAndNotEqualToNotInArrayRector::class,
        NegatedAndsToPositiveOrsRector::class,
        ReturnEarlyIfVariableRector::class,
        ChangeIfElseValueAssignToEarlyReturnRector::class,
        ParentTestClassConstructorRector::class, // misfires on test classes, breaking phpunit
        FunctionFirstClassCallableRector::class => [
            __DIR__ . '/src/Context/DebugScope.php',
        ],
    ]);
};
