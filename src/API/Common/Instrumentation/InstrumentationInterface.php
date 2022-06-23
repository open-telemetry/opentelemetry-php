<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Instrumentation;

use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use Psr\Log\LoggerInterface;

interface InstrumentationInterface
{

    public function getName(): string;

    public function getVersion(): ?string;

    public function getSchema(): ?string;

    public function register(): bool;

    public function activate(): bool;

    public function setPropagator(TextMapPropagatorInterface $propagator): void;

    public function getPropagator(): TextMapPropagatorInterface;

    public function setTracerProvider(TracerProviderInterface $tracerProvider): void;

    public function getTracerProvider(): TracerProviderInterface;

    public function setMeterProvider(MeterProviderInterface $meterProvider): void;

    public function getMeterProvider(): MeterProviderInterface;

    public function setLogger(LoggerInterface $logger): void;

    public function getLogger(): LoggerInterface;
}
