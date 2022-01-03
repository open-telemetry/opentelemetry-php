<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\API\Metrics as API;
use OpenTelemetry\Contrib\Prometheus\PrometheusExporter;
use OpenTelemetry\SDK\Metrics\Counter;
use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use Prometheus\Counter as PrometheusCounter;

class PrometheusExporterTest extends TestCase
{
    public function test_empty_metrics_export_returns_success(): void
    {
        $exporter = new PrometheusExporter($this->createMock(CollectorRegistry::class));

        $this->assertEquals(
            API\ExporterInterface::SUCCESS,
            $exporter->export([])
        );
    }

    /**
     * @dataProvider provideCounterData
     */
    public function test_prometheus_registry_method_is_called_for_counter_export(int $count, array $labels): void
    {
        $counter = new Counter('prometheus_test_counter');
        $counter->add($count);
        $counter->setLabels($labels);

        $prometheusCouner = $this->createMock(PrometheusCounter::class);
        $prometheusCouner->expects($this->once())
            ->method('incBy')
            ->with($count, $labels);

        $registry = $this->createMock(CollectorRegistry::class);
        $registry->expects($this->once())
            ->method('getOrRegisterCounter')
            ->willReturn($prometheusCouner);

        $exporter = new PrometheusExporter($registry);

        $this->assertEquals(
            API\ExporterInterface::SUCCESS,
            $exporter->export([$counter])
        );
    }

    public function provideCounterData(): array
    {
        return [
            [1, []],
            [7, ['label_a']],
            [12, ['label_a', 'label_b']],
        ];
    }
}
