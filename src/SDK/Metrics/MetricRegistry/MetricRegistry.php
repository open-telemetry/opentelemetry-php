<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\MetricRegistry;

use Closure;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextStorageInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Common\Time\ClockInterface;
use OpenTelemetry\SDK\Metrics\Stream\MetricAggregatorFactoryInterface;
use OpenTelemetry\SDK\Metrics\Stream\MetricAggregatorInterface;
use OpenTelemetry\SDK\Metrics\Stream\MetricCollectorInterface;
use OpenTelemetry\SDK\Metrics\Stream\MetricStreamInterface;
use function array_key_last;

final class MetricRegistry implements MetricRegistryInterface, MetricWriterInterface {

    private ?ContextStorageInterface $contextStorage;
    private AttributesFactoryInterface $attributesFactory;
    private ClockInterface $clock;

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
        ?ContextStorageInterface $contextStorage,
        AttributesFactoryInterface $attributesFactory,
        ClockInterface $clock
    ) {
        $this->contextStorage = $contextStorage;
        $this->attributesFactory = $attributesFactory;
        $this->clock = $clock;
    }

    public function registerSynchronousStream(int $instrumentId, MetricStreamInterface $stream, MetricCollectorInterface $collector): int {
        $this->streams[] = $stream;
        $streamId = array_key_last($this->streams);

        $this->synchronousAggregators[$streamId] = $collector;
        $this->instrumentToStreams[$instrumentId][$streamId] = $streamId;
        $this->streamToInstrument[$streamId] = $instrumentId;

        return $streamId;
    }

    public function registerAsynchronousStream(int $instrumentId, MetricStreamInterface $stream, MetricAggregatorFactoryInterface $aggregatorFactory): int {
        $this->streams[] = $stream;
        $streamId = array_key_last($this->streams);

        $this->asynchronousAggregatorFactories[$streamId] = $aggregatorFactory;
        $this->instrumentToStreams[$instrumentId][$streamId] = $streamId;
        $this->streamToInstrument[$streamId] = $instrumentId;

        return $streamId;
    }

    public function unregisterStream(int $streamId): void {
        $instrumentId = $this->streamToInstrument[$streamId];
        unset(
            $this->streams[$streamId],
            $this->synchronousAggregators[$streamId],
            $this->asynchronousAggregatorFactories[$streamId],
            $this->instrumentToStreams[$instrumentId][$streamId],
            $this->streamToInstrument[$streamId],
        );
        if (!$this->instrumentToStreams[$instrumentId]) {
            unset($this->instrumentToStreams[$instrumentId]);
        }
    }

    public function record(int $instrumentId, $value, iterable $attributes = [], $context = null): void {
        $context = Context::resolve($context, $this->contextStorage);
        $attributes = $this->attributesFactory->builder($attributes)->build();
        $timestamp = $this->clock->now();
        foreach ($this->instrumentToStreams[$instrumentId] ?? [] as $streamId) {
            if ($aggregator = $this->synchronousAggregators[$streamId] ?? null) {
                $aggregator->record($value, $attributes, $context, $timestamp);
            }
        }
    }

    public function registerCallback(Closure $callback, int ...$instrumentIds): int {
        $callbackId = array_key_last($this->asynchronousCallbacks) + 1;
        $this->asynchronousCallbacks[$callbackId] = $callback;
        $this->asynchronousCallbackArguments[$callbackId] = $instrumentIds;

        foreach ($instrumentIds as $instrumentId) {
            $this->instrumentToCallbacks[$instrumentId][$callbackId] = $callbackId;
        }

        return $callbackId;
    }

    public function unregisterCallback(int $callbackId): void {
        $instrumentIds = $this->asynchronousCallbackArguments[$callbackId];
        unset(
            $this->asynchronousCallbacks[$callbackId],
            $this->asynchronousCallbackArguments[$callbackId],
        );
        foreach ($instrumentIds as $instrumentId) {
            unset($this->instrumentToCallbacks[$instrumentId][$callbackId]);
            if (!$this->instrumentToCallbacks[$instrumentId]) {
                unset($this->instrumentToCallbacks[$instrumentId]);
            }
        }
    }

    public function collectAndPush(array $streamIds): void {
        $timestamp = $this->clock->now();
        $collectors = [];
        $observers = [];
        $callbackIds = [];
        foreach ($streamIds as $streamId) {
            if (!$collector = $this->synchronousAggregators[$streamId] ?? null) {
                $collector = $this->asynchronousAggregatorFactories[$streamId]->create();

                $instrumentId = $this->streamToInstrument[$streamId];
                $observers[$instrumentId] ??= new MultiObserver($this->attributesFactory, $timestamp);
                $observers[$instrumentId]->writers[] = $collector;
                foreach ($this->instrumentToCallbacks[$instrumentId] ?? [] as $callbackId) {
                    $callbackIds[$callbackId] = $callbackId;
                }
            }

            $collectors[$streamId] = $collector;
        }

        $noopObserver = new NoopObserver();
        $callbacks = [];
        foreach ($callbackIds as $callbackId) {
            $args = [];
            foreach ($this->asynchronousCallbackArguments[$callbackId] as $instrumentId) {
                $args[] = $observers[$instrumentId] ?? $noopObserver;
            }
            $callback = $this->asynchronousCallbacks[$callbackId];
            $callbacks[] = static fn() => $callback(...$args);
        }
        foreach ($callbacks as $callback) {
            $callback();
        }

        $timestamp = $this->clock->now();
        foreach ($collectors as $streamId => $collector) {
            if ($stream = $this->streams[$streamId] ?? null) {
                $stream->push($collector->collect($timestamp));
            }
        }
    }
}
