<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\Stream;

use OpenTelemetry\Context;
use OpenTelemetry\Metrics\Observer;
use OpenTelemetry\SDK\AttributesFactory;

final class AsynchronousMetricStreamObserver implements Observer {

    private WritableMetricStream $stream;
    private AttributesFactory $attributes;
    private int $timestamp;

    public function __construct(WritableMetricStream $stream, AttributesFactory $attributes, int $timestamp) {
        $this->stream = $stream;
        $this->attributes = $attributes;
        $this->timestamp = $timestamp;
    }

    public function observe(float|int $amount, iterable $attributes = []): void {
        $this->stream->record($amount, $this->attributes->builder($attributes)->build(), Context::static::empty(), $this->timestamp);
    }
}
