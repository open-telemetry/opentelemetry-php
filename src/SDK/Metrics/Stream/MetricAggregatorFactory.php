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
    public function __construct(
        private readonly ?AttributeProcessorInterface $attributeProcessor,
        private readonly AggregationInterface $aggregation,
    ) {
    }

    #[\Override]
    public function create(): MetricAggregatorInterface
    {
        return new MetricAggregator($this->attributeProcessor, $this->aggregation);
    }
}
