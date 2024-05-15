<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK;

use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\API\Metrics\Noop\NoopObservableCounter;
use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\SDK\Metrics\Data\Sum;
use OpenTelemetry\SDK\Metrics\Meter;
use OpenTelemetry\SDK\Metrics\MeterProviderBuilder;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporter;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

#[CoversClass(Meter::class)]
final class MeterTest extends TestCase
{
    public function test_batch_observe_observes_all_provided_instruments(): void
    {
        $exporter = new InMemoryExporter();
        $reader = new ExportingReader($exporter);
        $mp = (new MeterProviderBuilder())
            ->addReader($reader)
            ->build();
        $m = $mp->getMeter('test');

        try {
            $m->batchObserve(
                static function (ObserverInterface $a, ObserverInterface $b): void {
                    $a->observe(3);
                    $b->observe(5);
                },
                $m->createObservableCounter('a'),
                $m->createObservableCounter('b'),
            );

            $reader->collect();

            $metrics = $this->sumMetricsToMap($exporter->collect());
            $this->assertSame(3, $metrics['a'] ?? 0);
            $this->assertSame(5, $metrics['b'] ?? 0);
        } finally {
            $mp->shutdown();
        }
    }

    public function test_batch_observe_calls_callback_only_once(): void
    {
        $reader = new ExportingReader(new InMemoryExporter());
        $mp = (new MeterProviderBuilder())
            ->addReader($reader)
            ->build();
        $m = $mp->getMeter('test');

        try {
            $c = 0;
            $m->batchObserve(
                static function () use (&$c): void {
                    $c++;
                },
                $m->createObservableCounter('a'),
                $m->createObservableCounter('b'),
            );

            $reader->collect();

            $this->assertSame(1, $c);
        } finally {
            $mp->shutdown();
        }
    }

    public function test_batch_observe_detach_detaches_callback(): void
    {
        $exporter = new InMemoryExporter();
        $reader = new ExportingReader($exporter);
        $mp = (new MeterProviderBuilder())
            ->addReader($reader)
            ->build();
        $m = $mp->getMeter('test');

        try {
            $c = $m->batchObserve(
                static function (ObserverInterface $a, ObserverInterface $b): void {
                    $a->observe(3);
                    $b->observe(5);
                },
                $m->createObservableCounter('a'),
                $m->createObservableCounter('b'),
            );

            $c->detach();
            $reader->collect();

            $metrics = $this->sumMetricsToMap($exporter->collect());
            $this->assertSame(0, $metrics['a'] ?? 0);
            $this->assertSame(0, $metrics['b'] ?? 0);
        } finally {
            $mp->shutdown();
        }
    }

    public function test_batch_observe_weakens_callback(): void
    {
        $exporter = new InMemoryExporter();
        $reader = new ExportingReader($exporter);
        $mp = (new MeterProviderBuilder())
            ->addReader($reader)
            ->build();
        $m = $mp->getMeter('test');

        try {
            $m->batchObserve(
                $object = new class() {
                    public function __invoke(ObserverInterface $a, ObserverInterface $b): void
                    {
                        $a->observe(3);
                        $b->observe(5);
                    }
                },
                $m->createObservableCounter('a'),
                $m->createObservableCounter('b'),
            );

            $reader->collect();

            $metrics = $this->sumMetricsToMap($exporter->collect(true));
            $this->assertSame(3, $metrics['a'] ?? 0);
            $this->assertSame(5, $metrics['b'] ?? 0);

            unset($object);
            $reader->collect();

            $metrics = $this->sumMetricsToMap($exporter->collect());
            $this->assertSame(0, $metrics['a'] ?? 0);
            $this->assertSame(0, $metrics['b'] ?? 0);
        } finally {
            $mp->shutdown();
        }
    }

    public function test_batch_observe_invalid_instrument(): void
    {
        $logWriter = $this->createMock(LogWriterInterface::class);
        $logWriter->expects($this->once())->method('write')->with(LogLevel::WARNING);

        $exporter = new InMemoryExporter();
        $reader = new ExportingReader($exporter);
        $mp = (new MeterProviderBuilder())
            ->addReader($reader)
            ->build();
        $m = $mp->getMeter('test');

        $previousLogWriter = Logging::logWriter();
        Logging::setLogWriter($logWriter);

        try {
            $m->batchObserve(
                static function (ObserverInterface $a, ObserverInterface $b): void {
                    $a->observe(3);
                    $b->observe(5);
                },
                new NoopObservableCounter(),
                $m->createObservableCounter('b'),
            );

            $reader->collect();

            $metrics = $this->sumMetricsToMap($exporter->collect());
            $this->assertSame(0, $metrics['a'] ?? 0);
            $this->assertSame(5, $metrics['b'] ?? 0);
        } finally {
            Logging::setLogWriter($previousLogWriter);
            $mp->shutdown();
        }
    }

    public function test_batch_observe_invalid_instrument_different_meter(): void
    {
        $logWriter = $this->createMock(LogWriterInterface::class);
        $logWriter->expects($this->once())->method('write')->with(LogLevel::WARNING);

        $exporter = new InMemoryExporter();
        $reader = new ExportingReader($exporter);
        $mp = (new MeterProviderBuilder())
            ->addReader($reader)
            ->build();
        $m = $mp->getMeter('test');

        $previousLogWriter = Logging::logWriter();
        Logging::setLogWriter($logWriter);

        try {
            $m->batchObserve(
                static function (ObserverInterface $a, ObserverInterface $b): void {
                    $a->observe(3);
                    $b->observe(5);
                },
                $mp->getMeter('different')->createObservableCounter('a'),
                $m->createObservableCounter('b'),
            );

            $reader->collect();

            $metrics = $this->sumMetricsToMap($exporter->collect());
            $this->assertSame(0, $metrics['a'] ?? 0);
            $this->assertSame(5, $metrics['b'] ?? 0);
        } finally {
            Logging::setLogWriter($previousLogWriter);
            $mp->shutdown();
        }
    }

    #[CoversNothing]
    public function test_batch_observe_detach_with_repeated_instrument_does_not_trigger_undefined_offset_warning(): void
    {
        $this->expectNotToPerformAssertions();

        $mp = (new MeterProviderBuilder())
            ->addReader(new ExportingReader(new InMemoryExporter()))
            ->build();
        $m = $mp->getMeter('test');

        try {
            $c = $m->batchObserve(static fn () => null, $m->createObservableCounter('a'), $m->createObservableCounter('a'));
            $c->detach();
        } finally {
            $mp->shutdown();
        }
    }

    /**
     * @return array<string, float|int>
     */
    private function sumMetricsToMap(array $metrics): array
    {
        $map = [];
        foreach ($metrics as $metric) {
            if ($metric->data instanceof Sum) {
                foreach ($metric->data->dataPoints as $dataPoint) {
                    $map[$metric->name] ??= $dataPoint->value;
                }
            }
        }

        return $map;
    }
}
