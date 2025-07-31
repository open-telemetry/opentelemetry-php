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
    /**
     * @param iterable<MetricSourceRegistryInterface> $registries
     */
    public function __construct(
        private readonly iterable $registries,
        private readonly StalenessHandlerInterface $stalenessHandler,
    ) {
    }

    #[\Override]
    public function register(MetricSourceProviderInterface $provider, MetricMetadataInterface $metadata): void
    {
        foreach ($this->registries as $registry) {
            $registry->add($provider, $metadata, $this->stalenessHandler);
        }
    }
}
