<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration;

use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\Noop\NoopMeterProvider;
use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Context used for component creation.
 */
final readonly class Context
{

    /**
     * @param TracerProviderInterface $tracerProvider tracer provider to use for self diagnostics
     * @param MeterProviderInterface $meterProvider meter provider to use for self diagnostics
     * @param LoggerInterface $logger logger to use for self diagnostics
     */
    public function __construct(
        public TracerProviderInterface $tracerProvider = new NoopTracerProvider(),
        public MeterProviderInterface $meterProvider = new NoopMeterProvider(),
        public LoggerInterface $logger = new NullLogger(),
    ) {
    }
}
