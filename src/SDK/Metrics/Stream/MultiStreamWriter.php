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
final class MultiStreamWriter implements MetricWriterInterface
{
    private ?ContextStorageInterface $contextStorage;
    private AttributesFactoryInterface $attributesFactory;

    private iterable $streams;

    /**
     * @param iterable<WritableMetricStreamInterface> $streams
     */
    public function __construct(?ContextStorageInterface $contextStorage, AttributesFactoryInterface $attributesFactory, iterable $streams)
    {
        $this->contextStorage = $contextStorage;
        $this->attributesFactory = $attributesFactory;
        $this->streams = $streams;
    }

    public function record($value, iterable $attributes, $context, int $timestamp): void
    {
        $context = Context::resolve($context, $this->contextStorage);
        $attributes = $this->attributesFactory->builder($attributes)->build();
        foreach ($this->streams as $stream) {
            $stream->record($value, $attributes, $context, $timestamp);
        }
    }
}
