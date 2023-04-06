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
    private LoggerSharedState $loggerSharedState;
    private InstrumentationScopeFactoryInterface $instrumentationScopeFactory;

    public function __construct(array $processors, InstrumentationScopeFactoryInterface $instrumentationScopeFactory, ?ResourceInfo $resource = null)
    {
        $this->loggerSharedState = new LoggerSharedState(
            $resource ?? ResourceInfoFactory::defaultResource(),
            (new LogRecordLimitsBuilder())->build(),
            $processors
        );
        $this->instrumentationScopeFactory = $instrumentationScopeFactory;
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/sdk.md#logger-creation
     */
    public function getLogger(string $name, ?string $version = null, ?string $schemaUrl = null, bool $includeTraceContext = true, iterable $attributes = []): LoggerInterface
    {
        if ($this->loggerSharedState->hasShutdown()) {
            return NoopLogger::getInstance();
        }
        $scope = $this->instrumentationScopeFactory->create($name, $version, $schemaUrl, $attributes);

        return new Logger($this->loggerSharedState, $scope, $includeTraceContext);
    }

    public function shutdown(CancellationInterface $cancellation = null): bool
    {
        return $this->loggerSharedState->shutdown($cancellation);
    }

    public function forceFlush(CancellationInterface $cancellation = null): bool
    {
        $result = true;
        foreach ($this->loggerSharedState->getProcessors() as $processor) {
            if (!$processor->forceFlush($cancellation)) {
                $result = false;
            }
        }

        return $result;
    }
}
