<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\Stream;

use OpenTelemetry\Context\Context;
use OpenTelemetry\ContextStorage;
use OpenTelemetry\SDK\AttributesFactory;
use OpenTelemetry\SDK\Metrics\MetricWriter;

final class MultiStreamWriter implements MetricWriter {

    private ?ContextStorage $contextStorage;
    private AttributesFactory $attributes;

    private iterable $streams;

    /**
     * @param iterable<WritableMetricStream> $streams
     */
    public function __construct(?ContextStorage $contextStorage, AttributesFactory $attributes, iterable $streams) {
        $this->contextStorage = $contextStorage;
        $this->attributes = $attributes;
        $this->streams = $streams;
    }

    public function record(float|int $value, iterable $attributes, Context|false|null $context, int $timestamp): void {
        $context = $context
            ?? ($this->contextStorage ?? Context::storage())->current()
            ?: Context::getRoot();

        $attributes = $this->attributes->builder($attributes)->build();
        foreach ($this->streams as $stream) {
            $stream->record($value, $attributes, $context, $timestamp);
        }
    }
}
