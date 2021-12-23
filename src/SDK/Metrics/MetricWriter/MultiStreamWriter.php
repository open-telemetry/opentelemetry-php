<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\MetricWriter;

use OpenTelemetry\Context;
use OpenTelemetry\SDK\AttributesFactory;
use OpenTelemetry\SDK\Clock;
use OpenTelemetry\SDK\Metrics\MetricWriter;
use OpenTelemetry\SDK\Metrics\Stream\WritableMetricStream;

final class MultiStreamWriter implements MetricWriter {

    private iterable $streams;
    private AttributesFactory $attributes;
    private Clock $clock;

    /**
     * @param iterable<WritableMetricStream> $streams
     */
    public function __construct(iterable $streams, AttributesFactory $attributes, Clock $clock) {
        $this->streams = $streams;
        $this->attributes = $attributes;
        $this->clock = $clock;
    }

    public function record(float|int $value, iterable $attributes = [], Context|false|null $context = null): void {
        $attributes = $this->attributes->builder($attributes)->build();
        $context = $context
            ?? Context::static::current()
            ?: Context::static::empty();
        $timestamp = $this->clock->nanotime();

        foreach ($this->streams as $stream) {
            $stream->record($value, $attributes, $context, $timestamp);
        }
    }
}
