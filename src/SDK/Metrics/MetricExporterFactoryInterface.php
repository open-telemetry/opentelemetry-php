<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

interface MetricExporterFactoryInterface
{
    public function create(): MetricExporterInterface;
    public function type(): string;
    public function priority(): int;
}
