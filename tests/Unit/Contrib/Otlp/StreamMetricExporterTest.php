<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Otlp;

use function fopen;
use function fseek;
use OpenTelemetry\Contrib\Otlp\StreamMetricExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Metrics\Data\Metric;
use OpenTelemetry\SDK\Metrics\Data\NumberDataPoint;
use OpenTelemetry\SDK\Metrics\Data\Sum;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use PHPUnit\Framework\TestCase;
use function stream_get_contents;

/**
 * @covers \OpenTelemetry\Contrib\Otlp\StreamMetricExporter
 */
final class StreamMetricExporterTest extends TestCase
{
    public function test_exporter_writes_metrics_in_otlp_json_format_with_trailing_newline(): void
    {
        $stream = fopen('php://memory', 'a+b');
        $exporter = new StreamMetricExporter($stream);

        $exporter->export([
            new Metric(
                new InstrumentationScope('test', null, null, Attributes::create([])),
                ResourceInfoFactory::emptyResource(),
                'test',
                null,
                null,
                new Sum([
                    new NumberDataPoint(5, Attributes::create([]), 17, 42),
                ], Temporality::DELTA, false)
            ),
        ]);

        fseek($stream, 0);
        $this->assertSame(<<<METRICS
            {"resourceMetrics":[{"resource":{},"scopeMetrics":[{"scope":{"name":"test"},"metrics":[{"name":"test","sum":{"dataPoints":[{"startTimeUnixNano":"17","timeUnixNano":"42","asInt":"5"}],"aggregationTemporality":"AGGREGATION_TEMPORALITY_DELTA"}}]}]}]}

            METRICS, stream_get_contents($stream));
    }

    public function test_exporter_appends_metrics(): void
    {
        $stream = fopen('php://memory', 'a+b');
        $exporter = new StreamMetricExporter($stream);

        $exporter->export([
            new Metric(
                new InstrumentationScope('test', null, null, Attributes::create([])),
                ResourceInfoFactory::emptyResource(),
                'test',
                null,
                null,
                new Sum([
                    new NumberDataPoint(5, Attributes::create([]), 17, 42),
                ], Temporality::DELTA, false)
            ),
        ]);
        $exporter->export([
            new Metric(
                new InstrumentationScope('test', null, null, Attributes::create([])),
                ResourceInfoFactory::emptyResource(),
                'test',
                null,
                null,
                new Sum([
                    new NumberDataPoint(7, Attributes::create([]), 42, 57),
                ], Temporality::DELTA, false)
            ),
        ]);

        fseek($stream, 0);
        $this->assertSame(<<<METRICS
            {"resourceMetrics":[{"resource":{},"scopeMetrics":[{"scope":{"name":"test"},"metrics":[{"name":"test","sum":{"dataPoints":[{"startTimeUnixNano":"17","timeUnixNano":"42","asInt":"5"}],"aggregationTemporality":"AGGREGATION_TEMPORALITY_DELTA"}}]}]}]}
            {"resourceMetrics":[{"resource":{},"scopeMetrics":[{"scope":{"name":"test"},"metrics":[{"name":"test","sum":{"dataPoints":[{"startTimeUnixNano":"42","timeUnixNano":"57","asInt":"7"}],"aggregationTemporality":"AGGREGATION_TEMPORALITY_DELTA"}}]}]}]}

            METRICS, stream_get_contents($stream));
    }
}
