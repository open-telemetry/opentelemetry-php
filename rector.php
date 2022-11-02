<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Array_\CallableThisArrayToAnonymousFunctionRector;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersion::PHP_74);

    $rectorConfig->paths([
        __DIR__ . '/src',
    ]);

    $rectorConfig->sets([
        SetList::PHP_74,
        SetList::CODE_QUALITY,
    ]);
    $rectorConfig->skip([
        CallableThisArrayToAnonymousFunctionRector::class => [
            __DIR__ . '/src/SDK/SdkBuilder.php',
        ],
    ]);
};
