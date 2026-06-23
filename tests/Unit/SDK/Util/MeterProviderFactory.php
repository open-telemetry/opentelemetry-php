<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Util;

use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Metrics\DefaultAggregationProviderInterface;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricReaderInterface;
use OpenTelemetry\SDK\Metrics\MetricSourceRegistryInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandler\ImmediateStalenessHandlerFactory;
use OpenTelemetry\SDK\Metrics\View\CriteriaViewRegistry;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;

final class MeterProviderFactory
{
    public static function create(ClockInterface $clock, MetricReaderInterface&MetricSourceRegistryInterface&DefaultAggregationProviderInterface $reader): MeterProvider
    {
        return new MeterProvider(
            null,
            ResourceInfoFactory::emptyResource(),
            $clock,
            Attributes::factory(),
            new InstrumentationScopeFactory(Attributes::factory()),
            [$reader],
            new CriteriaViewRegistry(),
            null,
            new ImmediateStalenessHandlerFactory(),
        );
    }
}
