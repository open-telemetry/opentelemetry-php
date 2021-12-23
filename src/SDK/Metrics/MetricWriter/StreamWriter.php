<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\MetricWriter;

use OpenTelemetry\Context;
use OpenTelemetry\SDK\AttributesFactory;
use OpenTelemetry\SDK\Clock;
use OpenTelemetry\SDK\Metrics\MetricWriter;
use OpenTelemetry\SDK\Metrics\Stream\WritableMetricStream;

final class StreamWriter implements MetricWriter {

    private WritableMetricStream $stream;
    private AttributesFactory $attributes;
    private Clock $clock;

    public function __construct(WritableMetricStream $stream, AttributesFactory $attributes, Clock $clock) {
        $this->stream = $stream;
        $this->attributes = $attributes;
        $this->clock = $clock;
    }

    public function record(float|int $value, iterable $attributes = [], Context|false|null $context = null): void {
        $attributes = $this->attributes->builder($attributes)->build();
        $context = $context
            ?? Context::static::current()
            ?: Context::static::empty();
        $timestamp = $this->clock->nanotime();

        $this->stream->record($value, $attributes, $context, $timestamp);
    }
}
