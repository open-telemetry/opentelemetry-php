<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

interface MetricSourceRegistry
{
    public function add(MetricSourceProvider $provider, MetricMetadata $metadata, StalenessHandler $stalenessHandler): void;
}
