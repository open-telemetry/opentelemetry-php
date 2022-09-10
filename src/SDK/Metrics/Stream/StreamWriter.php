<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricWriterInterface;

/**
 * @internal
 */
final class StreamWriter implements MetricWriterInterface
{
    private ?ContextStorageInterface $contextStorage;
    private AttributesFactoryInterface $attributesFactory;

    private WritableMetricStreamInterface $stream;

    public function __construct(?ContextStorageInterface $contextStorage, AttributesFactoryInterface $attributesFactory, WritableMetricStreamInterface $stream)
    {
        $this->contextStorage = $contextStorage;
        $this->attributesFactory = $attributesFactory;
        $this->stream = $stream;
    }

    public function record($value, iterable $attributes, $context, int $timestamp): void
    {
        $context = Context::resolve($context, $this->contextStorage);
        $attributes = $this->attributesFactory->builder($attributes)->build();
        $this->stream->record($value, $attributes, $context, $timestamp);
    }
}
