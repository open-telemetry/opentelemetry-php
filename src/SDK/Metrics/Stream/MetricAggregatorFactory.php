<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\AttributeProcessorInterface;

/**
 * @internal
 */
final readonly class MetricAggregatorFactory implements MetricAggregatorFactoryInterface
{
    public function __construct(
        private ?AttributeProcessorInterface $attributeProcessor,
        private AggregationInterface $aggregation,
    ) {
    }

    public function create(): MetricAggregatorInterface
    {
        return new MetricAggregator($this->attributeProcessor, $this->aggregation);
    }
}
