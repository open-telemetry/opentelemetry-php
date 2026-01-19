<?php

declare(strict_types=1);

/**
 * If OTEL_PHP_AUTOLOAD_ENABLED=true, there may be compatability issues
 * if the version of PSR-3 installed in ./vendor conflicts with that of the
 * packaged composer PSR-3 library.
 *
 * If COMPOSER_DEV_MODE is present, then we can assume that a composer script
 * is running, and we can prevent the PSR-3 compatability issues by disabling
 * the SDK from activating.
 *
 * @see https://github.com/open-telemetry/opentelemetry-php/issues/1673
 */
if (\OpenTelemetry\SDK\Common\Util\ComposerHandler::isRunning() === false) {
    \OpenTelemetry\SDK\SdkAutoloader::autoload();
}
