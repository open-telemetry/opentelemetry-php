<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\NoopLogger;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactoryInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use WeakMap;

class LoggerProvider implements LoggerProviderInterface
{
    private readonly LoggerSharedState $loggerSharedState;
    private readonly WeakMap $loggers;

    /**
     * @param Configurator<LoggerConfig>|null $configurator
     */
    public function __construct(
        LogRecordProcessorInterface $processor,
        private readonly InstrumentationScopeFactoryInterface $instrumentationScopeFactory,
        ?ResourceInfo $resource = null,
        private ?Configurator $configurator = null,
    ) {
        $this->loggerSharedState = new LoggerSharedState(
            $resource ?? ResourceInfoFactory::defaultResource(),
            (new LogRecordLimitsBuilder())->build(),
            $processor
        );
        $this->loggers = new WeakMap();
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/sdk.md#logger-creation
     */
    #[\Override]
    public function getLogger(string $name, ?string $version = null, ?string $schemaUrl = null, iterable $attributes = []): LoggerInterface
    {
        if ($this->loggerSharedState->hasShutdown()) {
            return NoopLogger::getInstance();
        }
        $scope = $this->instrumentationScopeFactory->create($name, $version, $schemaUrl, $attributes);
        $logger = new Logger($this->loggerSharedState, $scope, $this->configurator);
        $this->loggers->offsetSet($logger, null);

        return $logger;
    }

    #[\Override]
    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        return $this->loggerSharedState->shutdown($cancellation);
    }

    #[\Override]
    public function forceFlush(?CancellationInterface $cancellation = null): bool
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
    #[\Override]
    public function updateConfigurator(Configurator $configurator): void
    {
        $this->configurator = $configurator;
        foreach ($this->loggers as $logger => $unused) {
            $logger->updateConfig($configurator);
        }
    }
}
