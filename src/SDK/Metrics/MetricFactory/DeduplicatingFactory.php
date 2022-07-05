<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricFactory;

use OpenTelemetry\SDK\InstrumentationScope;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\MetricFactory;
use function serialize;

final class DeduplicatingFactory implements MetricFactory
{
    private MetricFactory $metricFactory;
    private array $observers = [];
    private array $writers = [];

    public function __construct(MetricFactory $metricFactory)
    {
        $this->metricFactory = $metricFactory;
    }

    public function createAsynchronousObserver(InstrumentationScope $instrumentationScope, Instrument $instrument, int $timestamp): array
    {
        $instrumentationScopeId = self::instrumentationScopeId($instrumentationScope);
        $instrumentId = self::instrumentId($instrument);

        if ($observer = $this->observers[$instrumentationScopeId][$instrumentId] ?? null) {
            return $observer;
        }

        $this->observers[$instrumentationScopeId][$instrumentId]
            = [, $stalenessHandler]
            = $observer
            = $this->metricFactory->createAsynchronousObserver($instrumentationScope, $instrument, $timestamp);

        $stalenessHandler->onStale(function () use ($instrumentationScopeId, $instrumentId): void {
            unset($this->observers[$instrumentationScopeId][$instrumentId]);
            if (!$this->observers[$instrumentationScopeId]) {
                unset($this->observers[$instrumentationScopeId]);
            }
        });

        return $observer;
    }

    public function createSynchronousWriter(InstrumentationScope $instrumentationScope, Instrument $instrument, int $timestamp): array
    {
        $instrumentationScopeId = self::instrumentationScopeId($instrumentationScope);
        $instrumentId = self::instrumentId($instrument);

        if ($writer = $this->writers[$instrumentationScopeId][$instrumentId] ?? null) {
            return $writer;
        }

        $this->writers[$instrumentationScopeId][$instrumentId]
            = [, $stalenessHandler]
            = $writer
            = $this->metricFactory->createSynchronousWriter($instrumentationScope, $instrument, $timestamp);

        $stalenessHandler->onStale(function () use ($instrumentationScopeId, $instrumentId): void {
            unset($this->writers[$instrumentationScopeId][$instrumentId]);
            if (!$this->writers[$instrumentationScopeId]) {
                unset($this->writers[$instrumentationScopeId]);
            }
        });

        return $writer;
    }

    private static function instrumentationScopeId(InstrumentationScope $instrumentationScope): string
    {
        return serialize($instrumentationScope);
    }

    private static function instrumentId(Instrument $instrument): string
    {
        return serialize($instrument);
    }
}
