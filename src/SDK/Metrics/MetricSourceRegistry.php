<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics;

interface MetricSourceRegistry {

    public function add(MetricSourceProvider&MetricMetadata $provider, StalenessHandler $stalenessHandler): void;
}
