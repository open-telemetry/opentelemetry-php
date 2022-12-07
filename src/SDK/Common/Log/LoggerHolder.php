<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Log;

use OpenTelemetry\API\Common\Instrumentation\Configurator;
use OpenTelemetry\API\Common\Instrumentation\Globals;
use Psr\Log\LoggerInterface;

/**
 * @deprecated use Globals::registerInitializer
 */
final class LoggerHolder
{
    public static function set(LoggerInterface $logger): void
    {
        trigger_error('Use Globals::registerInitializer', E_USER_DEPRECATED);
        Globals::registerInitializer(fn (Configurator $configurator) => $configurator->withLogger($logger));
    }
}
