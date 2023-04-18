<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;

/**
 * Note that this logger class is deliberately NOT psr-3 compatible, per spec: "Note: this document defines a log
 * backend API. The API is not intended to be called by application developers directly."
 *
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/bridge-api.md
 */
class Logger implements LoggerInterface
{
    private InstrumentationScopeInterface $scope;
    private LoggerSharedState $loggerSharedState;

    public function __construct(LoggerSharedState $loggerSharedState, InstrumentationScopeInterface $scope)
    {
        $this->loggerSharedState = $loggerSharedState;
        $this->scope = $scope;
    }

    public function emit(LogRecord $logRecord): void
    {
        $readWriteLogRecord = new ReadWriteLogRecord($this->scope, $this->loggerSharedState, $logRecord);
        // @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/sdk.md#onemit
        $this->loggerSharedState->getProcessor()->onEmit(
            $readWriteLogRecord,
            $readWriteLogRecord->getContext(),
        );
    }
}
