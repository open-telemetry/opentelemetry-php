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
final class RegistryRegistration implements MetricRegistrationInterface
{
    private MetricSourceRegistryInterface $registry;
    private StalenessHandlerInterface $stalenessHandler;

    public function __construct(MetricSourceRegistryInterface $registry, StalenessHandlerInterface $stalenessHandler)
    {
        $this->registry = $registry;
        $this->stalenessHandler = $stalenessHandler;
    }

    public function register(MetricSourceProviderInterface $provider, MetricMetadataInterface $metadata): void
    {
        $this->registry->add($provider, $metadata, $this->stalenessHandler);
    }
}
