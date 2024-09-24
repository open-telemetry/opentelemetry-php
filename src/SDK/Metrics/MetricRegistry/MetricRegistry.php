<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricRegistry;

use function array_key_last;
use Closure;
use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\Stream\MetricAggregatorFactoryInterface;
use OpenTelemetry\SDK\Metrics\Stream\MetricAggregatorInterface;
use OpenTelemetry\SDK\Metrics\Stream\MetricStreamInterface;
use function spl_object_id;

/**
 * @internal
 */
final class MetricRegistry implements MetricRegistryInterface, MetricWriterInterface
{
    /** @var array<int, MetricStreamInterface> */
    private array $streams = [];
    /** @var array<int, MetricAggregatorInterface> */
    private array $synchronousAggregators = [];
    /** @var array<int, MetricAggregatorFactoryInterface> */
    private array $asynchronousAggregatorFactories = [];

    /** @var array<int, array<int, int>> */
    private array $instrumentToStreams = [];
    /** @var array<int, int> */
    private array $streamToInstrument = [];
    /** @var array<int, array<int, int>> */
    private array $instrumentToCallbacks = [];
    /** @var array<int, Closure> */
    private array $asynchronousCallbacks = [];
    /** @var array<int, list<int>> */
    private array $asynchronousCallbackArguments = [];

    public function __construct(
        private readonly ?ContextStorageInterface $contextStorage,
        private readonly AttributesFactoryInterface $attributesFactory,
        private readonly ClockInterface $clock,
    ) {
    }

    public function registerSynchronousStream(Instrument $instrument, MetricStreamInterface $stream, MetricAggregatorInterface $aggregator): int
    {
        $this->streams[] = $stream;
        $streamId = array_key_last($this->streams);
        $instrumentId = spl_object_id($instrument);

        $this->synchronousAggregators[$streamId] = $aggregator;
        $this->instrumentToStreams[$instrumentId][$streamId] = $streamId;
        $this->streamToInstrument[$streamId] = $instrumentId;

        return $streamId;
    }

    public function registerAsynchronousStream(Instrument $instrument, MetricStreamInterface $stream, MetricAggregatorFactoryInterface $aggregatorFactory): int
    {
        $this->streams[] = $stream;
        $streamId = array_key_last($this->streams);
        $instrumentId = spl_object_id($instrument);

        $this->asynchronousAggregatorFactories[$streamId] = $aggregatorFactory;
        $this->instrumentToStreams[$instrumentId][$streamId] = $streamId;
        $this->streamToInstrument[$streamId] = $instrumentId;

        return $streamId;
    }

    public function unregisterStreams(Instrument $instrument): array
    {
        $instrumentId = spl_object_id($instrument);
        $streamIds = $this->instrumentToStreams[$instrumentId] ?? [];

        foreach ($streamIds as $streamId) {
            unset(
                $this->streams[$streamId],
                $this->synchronousAggregators[$streamId],
                $this->asynchronousAggregatorFactories[$streamId],
                $this->streamToInstrument[$streamId],
            );
        }
        unset($this->instrumentToStreams[$instrumentId]);

        return $streamIds;
    }

    public function record(Instrument $instrument, $value, iterable $attributes = [], $context = null): void
    {
        $context = Context::resolve($context, $this->contextStorage);
        $attributes = $this->attributesFactory->builder($attributes)->build();
        $timestamp = $this->clock->now();
        $instrumentId = spl_object_id($instrument);
        foreach ($this->instrumentToStreams[$instrumentId] ?? [] as $streamId) {
            if ($aggregator = $this->synchronousAggregators[$streamId] ?? null) {
                $aggregator->record($value, $attributes, $context, $timestamp);
            }
        }
    }

    public function registerCallback(Closure $callback, Instrument $instrument, Instrument ...$instruments): int
    {
        $callbackId = array_key_last($this->asynchronousCallbacks) + 1;
        $this->asynchronousCallbacks[$callbackId] = $callback;

        $instrumentId = spl_object_id($instrument);
        $this->asynchronousCallbackArguments[$callbackId] = [$instrumentId];
        $this->instrumentToCallbacks[$instrumentId][$callbackId] = $callbackId;
        foreach ($instruments as $instrument) {
            $instrumentId = spl_object_id($instrument);
            $this->asynchronousCallbackArguments[$callbackId][] = $instrumentId;
            $this->instrumentToCallbacks[$instrumentId][$callbackId] = $callbackId;
        }

        return $callbackId;
    }

    public function unregisterCallback(int $callbackId): void
    {
        $instrumentIds = $this->asynchronousCallbackArguments[$callbackId];
        unset(
            $this->asynchronousCallbacks[$callbackId],
            $this->asynchronousCallbackArguments[$callbackId],
        );
        foreach ($instrumentIds as $instrumentId) {
            unset($this->instrumentToCallbacks[$instrumentId][$callbackId]);
            if (!($this->instrumentToCallbacks[$instrumentId] ?? [])) {
                unset($this->instrumentToCallbacks[$instrumentId]);
            }
        }
    }

    public function collectAndPush(iterable $streamIds): void
    {
        $timestamp = $this->clock->now();
        $aggregators = [];
        $observers = [];
        $callbackIds = [];
        foreach ($streamIds as $streamId) {
            $instrumentId = $this->streamToInstrument[$streamId];
            if (!$aggregator = $this->synchronousAggregators[$streamId] ?? null) {
                $aggregator = $this->asynchronousAggregatorFactories[$streamId]->create();

                $observers[$instrumentId] ??= new MultiObserver($this->attributesFactory, $timestamp);
                $observers[$instrumentId]->writers[] = $aggregator;
                foreach ($this->instrumentToCallbacks[$instrumentId] ?? [] as $callbackId) {
                    $callbackIds[$callbackId] = $callbackId;
                }
            }

            $aggregators[$streamId] = $aggregator;
        }

        $noopObserver = new NoopObserver();
        $callbacks = [];
        foreach ($callbackIds as $callbackId) {
            $args = [];
            foreach ($this->asynchronousCallbackArguments[$callbackId] as $instrumentId) {
                $args[] = $observers[$instrumentId] ?? $noopObserver;
            }
            $callback = $this->asynchronousCallbacks[$callbackId];
            $callbacks[] = static fn () => $callback(...$args);
        }
        foreach ($callbacks as $callback) {
            $callback();
        }

        $timestamp = $this->clock->now();
        foreach ($aggregators as $streamId => $aggregator) {
            if ($stream = $this->streams[$streamId] ?? null) {
                $stream->push($aggregator->collect($timestamp));
            }
        }
    }

    public function enabled(Instrument $instrument): bool
    {
        return isset($this->instrumentToStreams[spl_object_id($instrument)]);
    }
}
