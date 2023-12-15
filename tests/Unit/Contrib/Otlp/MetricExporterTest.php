<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Otlp;

use function fopen;
use function fseek;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransport;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Metrics\Data\Metric;
use OpenTelemetry\SDK\Metrics\Data\NumberDataPoint;
use OpenTelemetry\SDK\Metrics\Data\Sum;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use PHPUnit\Framework\TestCase;
use function stream_get_contents;

/**
 * @covers \OpenTelemetry\Contrib\Otlp\MetricExporter
 */
final class MetricExporterTest extends TestCase
{
    private $stream;
    private MetricExporter $exporter;

    public function setUp(): void
    {
        $this->stream = fopen('php://memory', 'a+b');
        $transport = new StreamTransport($this->stream, 'application/x-ndjson');
        $this->exporter = new MetricExporter($transport);
    }

    public function test_exporter_writes_metrics_in_otlp_json_format_with_trailing_newline(): void
    {
        $this->exporter->export([
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

        fseek($this->stream, 0);
        $this->assertSame(<<<METRICS
            {"resourceMetrics":[{"resource":{},"scopeMetrics":[{"scope":{"name":"test"},"metrics":[{"name":"test","sum":{"dataPoints":[{"startTimeUnixNano":"17","timeUnixNano":"42","asInt":"5"}],"aggregationTemporality":1}}]}]}]}

            METRICS, stream_get_contents($this->stream));
    }

    public function test_exporter_appends_metrics(): void
    {
        $this->exporter->export([
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
        $this->exporter->export([
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

        fseek($this->stream, 0);
        $this->assertSame(<<<METRICS
            {"resourceMetrics":[{"resource":{},"scopeMetrics":[{"scope":{"name":"test"},"metrics":[{"name":"test","sum":{"dataPoints":[{"startTimeUnixNano":"17","timeUnixNano":"42","asInt":"5"}],"aggregationTemporality":1}}]}]}]}
            {"resourceMetrics":[{"resource":{},"scopeMetrics":[{"scope":{"name":"test"},"metrics":[{"name":"test","sum":{"dataPoints":[{"startTimeUnixNano":"42","timeUnixNano":"57","asInt":"7"}],"aggregationTemporality":1}}]}]}]}

            METRICS, stream_get_contents($this->stream));
    }
}
