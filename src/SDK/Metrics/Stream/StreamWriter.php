<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricWriter;

final class StreamWriter implements MetricWriter
{
    private ?ContextStorageInterface $contextStorage;
    private AttributesFactoryInterface $attributesFactory;

    private WritableMetricStream $stream;

    public function __construct(?ContextStorageInterface $contextStorage, AttributesFactoryInterface $attributesFactory, WritableMetricStream $stream)
    {
        $this->contextStorage = $contextStorage;
        $this->attributesFactory = $attributesFactory;
        $this->stream = $stream;
    }

    public function record($value, iterable $attributes, $context, int $timestamp): void
    {
        $context = $context
            ?? ($this->contextStorage ?? Context::storage())->current()
            ?: Context::getRoot();

        $attributes = $this->attributesFactory->builder($attributes)->build();
        $this->stream->record($value, $attributes, $context, $timestamp);
    }
}
