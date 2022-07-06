<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

use OpenTelemetry\API\Metrics\Observer;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;

final class AsynchronousMetricStreamObserver implements Observer
{
    private WritableMetricStream $stream;
    private AttributesFactoryInterface $attributesFactory;
    private int $timestamp;

    public function __construct(WritableMetricStream $stream, AttributesFactoryInterface $attributesFactory, int $timestamp)
    {
        $this->stream = $stream;
        $this->attributesFactory = $attributesFactory;
        $this->timestamp = $timestamp;
    }

    public function observe($amount, iterable $attributes = []): void
    {
        $this->stream->record($amount, $this->attributesFactory->builder($attributes)->build(), Context::getRoot(), $this->timestamp);
    }
}
