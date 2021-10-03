<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Metrics\Exporters;

use OpenTelemetry\API\Metrics as API;
use OpenTelemetry\SDK\Metrics\Counter;
use OpenTelemetry\SDK\Metrics\Exporters\PrometheusExporter;
use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use Prometheus\Counter as PrometheusCounter;

class PrometheusExporterTest extends TestCase
{
    public function testEmptyMetricsExportReturnsSuccess()
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
    public function testPrometheusRegistryMethodIsCalledForCounterExport(int $count, array $labels)
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
