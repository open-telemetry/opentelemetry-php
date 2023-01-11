<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common;

use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;

class Instrumentation
{
    use LogsMessagesTrait;

    public static function isDisabled(string $name): bool
    {
        if (in_array($name, Configuration::getList(Variables::OTEL_PHP_DISABLED_INSTRUMENTATIONS))) {
            self::logInfo('Instrumentation disabled by config: ' . $name);

            return true;
        }

        return false;
    }
}
