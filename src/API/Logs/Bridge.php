<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

use OpenTelemetry\API\Common\Instrumentation\Globals;

/**
 * OpenTelemetry logs bridge API. To be used when implementing a handler/appender for existing logging libraries.
 * This API is not meant to be used by application developers, but rather by library developers for the purpose
 * of integrating existing logging libraries with OpenTelemetry.
 *
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/bridge-api.md
 */
class Bridge
{
    private LoggerInterface $logger;

    public function __construct(string $name, ?LoggerProviderInterface $loggerProvider = null)
    {
        if ($loggerProvider === null) {
            $loggerProvider = Globals::loggerProvider();
        }
        $this->logger = $loggerProvider->getLogger($name);
    }

    /**
     * Emit a LogRecord to an OpenTelemetry Logger
     */
    public function emit(LogRecord $logRecord): void
    {
        $this->logger->logRecord($logRecord);
    }
}
