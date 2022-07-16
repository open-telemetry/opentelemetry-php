<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;

/**
 * @internal
 */
final class AsynchronousMetricStreamObserver implements ObserverInterface
{
    private WritableMetricStreamInterface $stream;
    private AttributesFactoryInterface $attributesFactory;
    private int $timestamp;

    public function __construct(WritableMetricStreamInterface $stream, AttributesFactoryInterface $attributesFactory, int $timestamp)
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
