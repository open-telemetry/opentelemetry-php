<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs\Map;

use OpenTelemetry\API\Logs\Severity;

class Psr3
{
    /**
     * Maps PSR-3 severity level (string) to the appropriate opentelemetry severity
     *
     * @deprecated Use Severity::fromPsr3
     */
    public static function severityNumber(string $level): Severity
    {
        return Severity::fromPsr3($level);
    }
}
