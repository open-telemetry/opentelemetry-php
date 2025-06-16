<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Configuration;

use function class_alias;
use OpenTelemetry\API\Logs\LoggerProviderInterface;
use OpenTelemetry\API\Logs\NoopLoggerProvider;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\Noop\NoopMeterProvider;
use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class Context
{
    public function __construct(
        public readonly TracerProviderInterface $tracerProvider = new NoopTracerProvider(),
        public readonly MeterProviderInterface $meterProvider = new NoopMeterProvider(),
        public readonly LoggerProviderInterface $loggerProvider = new NoopLoggerProvider(),
        public readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }
}

/** @phpstan-ignore-next-line @phan-suppress-next-line PhanUndeclaredClassReference */
class_alias(Context::class, \OpenTelemetry\Config\SDK\Configuration\Context::class);
