<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Util;

use function basename;
use function getenv;
use function in_array;

final class ComposerHandler
{
    public static function isRunning(): bool
    {
        /**
         * This is set when composer is running a script; eg:
         * composer run-script test-psr3
         *
         * However, it is not set in the following case:
         * composer test-psr3
         */
        if (getenv('COMPOSER_DEV_MODE')) {
            return true;
        }

        if (
            ($entrypoint = $_SERVER['argv'][0] ?? '') === getenv('COMPOSER_BINARY')
            || in_array(basename($entrypoint), ['composer', 'composer.phar'], true)
        ) {
            return true;
        }

        return false;
    }
}
