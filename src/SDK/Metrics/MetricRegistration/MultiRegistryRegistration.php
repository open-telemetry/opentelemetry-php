<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricRegistration;

use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use OpenTelemetry\SDK\Metrics\MetricRegistrationInterface;
use OpenTelemetry\SDK\Metrics\MetricSourceProviderInterface;
use OpenTelemetry\SDK\Metrics\MetricSourceRegistryInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandlerInterface;

/**
 * @internal
 */
final class MultiRegistryRegistration implements MetricRegistrationInterface
{
    private iterable $registries;
    private StalenessHandlerInterface $stalenessHandler;

    /**
     * @param iterable<MetricSourceRegistryInterface> $registries
     */
    public function __construct(iterable $registries, StalenessHandlerInterface $stalenessHandler)
    {
        $this->registries = $registries;
        $this->stalenessHandler = $stalenessHandler;
    }

    public function register(MetricSourceProviderInterface $provider, MetricMetadataInterface $metadata): void
    {
        foreach ($this->registries as $registry) {
            $registry->add($provider, $metadata, $this->stalenessHandler);
        }
    }
}
