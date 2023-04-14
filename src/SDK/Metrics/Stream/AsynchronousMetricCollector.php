<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\AttributeProcessorInterface;

/**
 * @internal
 */
final class AsynchronousMetricCollector implements MetricCollectorInterface
{
    private $instrument;
    private ?AttributeProcessorInterface $attributeProcessor;
    private AggregationInterface $aggregation;
    private AttributesFactoryInterface $attributesFactory;

    public function __construct(
        callable $instrument,
        ?AttributeProcessorInterface $attributeProcessor,
        AggregationInterface $aggregation,
        AttributesFactoryInterface $attributesFactory
    ) {
        $this->instrument = $instrument;
        $this->attributeProcessor = $attributeProcessor;
        $this->aggregation = $aggregation;
        $this->attributesFactory = $attributesFactory;
    }

    public function collect(int $timestamp): Metric
    {
        $aggregator = new MetricAggregator($this->attributeProcessor, $this->aggregation);
        ($this->instrument)(new AsynchronousMetricStreamObserver($aggregator, $this->attributesFactory, $timestamp));

        return $aggregator->collect($timestamp);
    }
}
