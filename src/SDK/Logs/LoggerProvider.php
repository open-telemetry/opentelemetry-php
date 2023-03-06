<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\API\Logs\LoggerInterface;
use OpenTelemetry\API\Logs\NoopLogger;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactory;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;

class LoggerProvider implements LoggerProviderInterface
{
    private LoggerSharedState $loggerSharedState;

    public function __construct(LogRecordProcessorInterface $processor, ?ResourceInfo $resource = null)
    {
        $this->loggerSharedState = new LoggerSharedState(
            $resource ?? ResourceInfoFactory::defaultResource(),
            (new LogRecordLimitsBuilder())->build(),
            $processor
        );
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/sdk.md#logger-creation
     */
    public function getLogger(string $name, ?string $version = null, ?string $schemaUrl = null, bool $includeTraceContext = true, iterable $attributes = []): LoggerInterface
    {
        if ($this->loggerSharedState->hasShutdown()) {
            return NoopLogger::getInstance();
        }
        $scope = new InstrumentationScope($name, $version, $schemaUrl, (new AttributesFactory())->builder($attributes)->build());

        return new Logger($this->loggerSharedState, $scope, $includeTraceContext);
    }

    public function shutdown(CancellationInterface $cancellation = null): bool
    {
        return $this->loggerSharedState->shutdown($cancellation);
    }

    public function forceFlush(CancellationInterface $cancellation = null): bool
    {
        return $this->loggerSharedState->getProcessor()->forceFlush($cancellation);
    }
}
