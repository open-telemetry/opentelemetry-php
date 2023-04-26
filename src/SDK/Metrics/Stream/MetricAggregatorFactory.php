<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\AttributeProcessorInterface;

/**
 * @internal
 */
final class MetricAggregatorFactory implements MetricAggregatorFactoryInterface
{
    private ?AttributeProcessorInterface $attributeProcessor;
    private AggregationInterface $aggregation;

    public function __construct(?AttributeProcessorInterface $attributeProcessor, AggregationInterface $aggregation)
    {
        $this->attributeProcessor = $attributeProcessor;
        $this->aggregation = $aggregation;
    }

    public function create(): MetricAggregatorInterface
    {
        return new MetricAggregator($this->attributeProcessor, $this->aggregation);
    }
}
