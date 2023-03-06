<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;

class Logger implements LoggerInterface
{
    private InstrumentationScope $scope;
    private LoggerSharedState $loggerSharedState;
    private bool $includeTraceContext;

    public function __construct(LoggerSharedState $loggerSharedState, InstrumentationScope $scope, bool $includeTraceContext)
    {
        $this->loggerSharedState = $loggerSharedState;
        $this->scope = $scope;
        $this->includeTraceContext = $includeTraceContext;
    }

    public function logRecord(LogRecord $logRecord): void
    {
        $readWriteLogRecord = new ReadWriteLogRecord($this->scope, $this->loggerSharedState, $logRecord->toLogRecordData());
        //@todo explicitly passed context required by spec: https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/sdk.md#logrecordprocessor-operations
        $this->loggerSharedState->getProcessor()->onEmit(
            $readWriteLogRecord,
            $this->includeTraceContext ? Context::getCurrent() : null,
        );
    }
}
