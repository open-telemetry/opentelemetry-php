<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\API\Logs\Map\Psr3;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use Psr\Log\LogLevel;

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

    public function emergency($message, array $context = [])
    {
        $this->logRecord($this->createLogRecord(LogLevel::EMERGENCY, $message, $context));
    }

    public function alert($message, array $context = [])
    {
        $this->logRecord($this->createLogRecord(LogLevel::ALERT, $message, $context));
    }

    public function critical($message, array $context = [])
    {
        $this->logRecord($this->createLogRecord(LogLevel::CRITICAL, $message, $context));
    }

    public function error($message, array $context = [])
    {
        $this->logRecord($this->createLogRecord(LogLevel::ERROR, $message, $context));
    }

    public function warning($message, array $context = [])
    {
        $this->logRecord($this->createLogRecord(LogLevel::WARNING, $message, $context));
    }

    public function notice($message, array $context = [])
    {
        $this->logRecord($this->createLogRecord(LogLevel::NOTICE, $message, $context));
    }

    public function info($message, array $context = [])
    {
        $this->logRecord($this->createLogRecord(LogLevel::INFO, $message, $context));
    }

    public function debug($message, array $context = [])
    {
        $this->logRecord($this->createLogRecord(LogLevel::DEBUG, $message, $context));
    }

    public function log($level, $message, array $context = [])
    {
        $this->logRecord($this->createLogRecord($level, $message, $context));
    }

    private function createLogRecord($level, $message, array $context): LogRecord
    {
        return (new LogRecord($message))
            ->setAttributes($context)
            ->setObservedTimestamp((int) (microtime(true) * LogRecord::NANOS_PER_SECOND))
            ->setSeverityNumber(Psr3::severityNumber($level))
            ->setSeverityText($level);
    }
}
