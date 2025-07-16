<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;

class Logger implements LoggerInterface
{
    use LogsMessagesTrait;
    private LoggerConfig $config;

    /**
     * @internal
     * @param Configurator<LoggerConfig>|null $configurator
     */
    public function __construct(
        private readonly LoggerSharedState $loggerSharedState,
        private readonly InstrumentationScopeInterface $scope,
        ?Configurator $configurator = null,
    ) {
        $this->config = $configurator ? $configurator->resolve($scope) : LoggerConfig::default();
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.44.0/specification/logs/api.md#emit-a-logrecord
     */
    #[\Override]
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

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.44.0/specification/logs/api.md#enabled
     */
    #[\Override]
    public function isEnabled(?ContextInterface $context = null, ?int $severityNumber = null, ?string $eventName = null): bool
    {
        return $this->config->isEnabled();
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.44.0/specification/logs/sdk.md#loggerconfigurator
     * @param Configurator<LoggerConfig> $configurator
     */
    public function updateConfig(Configurator $configurator): void
    {
        $this->config = $configurator->resolve($this->scope);
    }
}
