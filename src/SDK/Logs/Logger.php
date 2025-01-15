<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\API\Logs\Severity;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;

/**
 * Note that this logger class is deliberately NOT psr-3 compatible, per spec: "Note: this document defines a log
 * backend API. The API is not intended to be called by application developers directly."
 *
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/bridge-api.md
 */
class Logger implements LoggerInterface
{
    use LogsMessagesTrait;
    private LoggerConfig $config;
    private ClockInterface $clock;

    /**
     * @internal
     * @param Configurator<LoggerConfig>|null $configurator
     */
    public function __construct(
        private readonly LoggerSharedState $loggerSharedState,
        private readonly InstrumentationScopeInterface $scope,
        ?Configurator $configurator = null,
        ?ClockInterface $clock = null,
    ) {
        $this->config = $configurator ? $configurator->resolve($scope) : LoggerConfig::default();
        $this->clock = $clock ?? Clock::getDefault();
    }

    public function emit(LogRecord $logRecord): void
    {
        //If a Logger is disabled, it MUST behave equivalently to No-op Logger.
        if ($this->isEnabled() === false) {
            return;
        }
        $readWriteLogRecord = new ReadWriteLogRecord($this->scope, $this->loggerSharedState, $logRecord);
        // @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/sdk.md#onemit
        $this->loggerSharedState->getProcessor()->onEmit(
            $readWriteLogRecord,
            $readWriteLogRecord->getContext(),
        );
        if ($readWriteLogRecord->getAttributes()->getDroppedAttributesCount()) {
            self::logWarning('Dropped log attributes', [
                'attributes' => $readWriteLogRecord->getAttributes()->getDroppedAttributesCount(),
            ]);
        }
    }

    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    /**
     * @param Configurator<LoggerConfig> $configurator
     */
    public function updateConfig(Configurator $configurator): void
    {
        $this->config = $configurator->resolve($this->scope);
    }

    public function emitEvent(
        string $name,
        ?int $timestamp = null,
        ?int $observerTimestamp = null,
        ?ContextInterface $context = null,
        ?Severity $severityNumber = null,
        ?string $severityText = null,
        mixed $body = null,
        iterable $attributes = [],
    ): void {
        $logRecord = new LogRecord();
        $logRecord->setEventName($name);
        $timestamp && $logRecord->setTimestamp($timestamp);
        $logRecord->setObservedTimestamp($observerTimestamp ?? $this->clock->now());
        $logRecord->setContext($context ?? Context::getCurrent());
        $severityNumber && $logRecord->setSeverityNumber($severityNumber);
        $severityText && $logRecord->setSeverityText($severityText);
        $body && $logRecord->setBody($body);
        $logRecord->setAttributes($attributes);

        $this->emit($logRecord);
    }
}
