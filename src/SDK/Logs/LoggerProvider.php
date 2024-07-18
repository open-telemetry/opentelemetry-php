<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\NoopLogger;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactoryInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;

class LoggerProvider implements LoggerProviderInterface
{
    private readonly LoggerSharedState $loggerSharedState;
    private readonly LoggerConfigurator $configurator;

    public function __construct(
        LogRecordProcessorInterface $processor,
        private readonly InstrumentationScopeFactoryInterface $instrumentationScopeFactory,
        ?ResourceInfo $resource = null,
        ?LoggerConfigurator $configurator = null,
    ) {
        $this->loggerSharedState = new LoggerSharedState(
            $resource ?? ResourceInfoFactory::defaultResource(),
            (new LogRecordLimitsBuilder())->build(),
            $processor
        );
        $this->configurator = $configurator ?? new LoggerConfigurator();
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
        if ($this->configurator->getConfig($scope)->isEnabled() === false) {
            return NoopLogger::getInstance();
        }

        return new Logger($this->loggerSharedState, $scope, $this->configurator->getConfig($scope));
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
}
