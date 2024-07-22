<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\NoopLogger;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactoryInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use OpenTelemetry\SDK\Common\InstrumentationScope\ScopeConfigurator;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use WeakMap;

class LoggerProvider implements LoggerProviderInterface
{
    private readonly LoggerSharedState $loggerSharedState;
    private readonly WeakMap $loggers;
    private ScopeConfigurator $configurator;

    public function __construct(
        LogRecordProcessorInterface $processor,
        private readonly InstrumentationScopeFactoryInterface $instrumentationScopeFactory,
        ?ResourceInfo $resource = null,
        ?ScopeConfigurator $configurator = null,
    ) {
        $this->loggerSharedState = new LoggerSharedState(
            $resource ?? ResourceInfoFactory::defaultResource(),
            (new LogRecordLimitsBuilder())->build(),
            $processor
        );
        $this->loggers = new WeakMap();
        $this->configurator = $configurator ?? Configurator::default();
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/sdk.md#logger-creation
     */
    public function getLogger(string $name, ?string $version = null, ?string $schemaUrl = null, iterable $attributes = []): LoggerInterface
    {
        if ($this->loggerSharedState->hasShutdown()) {
            return NoopLogger::getInstance();
        }
        $scope = $this->instrumentationScopeFactory->create($name, $version, $schemaUrl, $attributes);
        $logger = new Logger($this->loggerSharedState, $scope, $this->configurator);
        $this->loggers->offsetSet($logger, $logger);

        return $logger;
    }

    public function shutdown(CancellationInterface $cancellation = null): bool
    {
        return $this->loggerSharedState->shutdown($cancellation);
    }

    public function forceFlush(CancellationInterface $cancellation = null): bool
    {
        return $this->loggerSharedState->forceFlush($cancellation);
    }

    public static function builder(): LoggerProviderBuilder
    {
        return new LoggerProviderBuilder();
    }

    /**
     * Update the {@link Configurator} for a {@link LoggerProvider}, which will
     * reconfigure all loggers created from the provider.
     * @experimental
     */
    public function updateConfigurator(ScopeConfigurator $configurator): void
    {
        $this->configurator = $configurator;
        foreach ($this->loggers as $logger) {
            $logger->updateConfig($configurator);
        }
    }
}
