<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\MetricExporter;

use function fopen;
use function fseek;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransport;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Metrics\Data\Histogram;
use OpenTelemetry\SDK\Metrics\Data\HistogramDataPoint;
use OpenTelemetry\SDK\Metrics\Data\Metric;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\MetricExporter\ConsoleMetricExporter;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use OpenTelemetry\SDK\Metrics\TemporalityPreference;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use function stream_get_contents;

#[CoversClass(ConsoleMetricExporter::class)]
final class ConsoleMetricExporterTest extends TestCase
{
    /** @var resource */
    private $stream;
    private ConsoleMetricExporter $exporter;

    #[\Override]
    public function setUp(): void
    {
        $stream = fopen('php://memory', 'a+b');
        $this->assertIsResource($stream);
        $this->stream = $stream;
        $this->exporter = new ConsoleMetricExporter(null, new StreamTransport($this->stream, 'application/json'));
    }

    public function test_exports_non_finite_values(): void
    {
        $this->assertTrue($this->exporter->export([
            new Metric(
                new InstrumentationScope('test', null, null, Attributes::create([])),
                ResourceInfoFactory::emptyResource(),
                'test',
                null,
                null,
                new Histogram([
                    new HistogramDataPoint(1, 5, NAN, NAN, [1, 0], [10, INF], Attributes::create(['foo' => 'bar']), 17, 42),
                ], Temporality::CUMULATIVE),
            ),
        ]));

        fseek($this->stream, 0);
        $output = json_decode(stream_get_contents($this->stream), true, flags: JSON_THROW_ON_ERROR);
        $dataPoint = $output['scope']['metrics'][0]['data']['dataPoints'][0];

        $this->assertSame('NaN', $dataPoint['min']);
        $this->assertSame('NaN', $dataPoint['max']);
        $this->assertSame([10, 'Infinity'], $dataPoint['explicitBounds']);
        $this->assertSame(['foo' => 'bar'], $dataPoint['attributes']);
    }

    public function test_shutdown_shuts_down_transport(): void
    {
        $this->assertTrue($this->exporter->shutdown());
        $this->assertFalse($this->exporter->shutdown());
    }

    public function test_export_after_shutdown_fails(): void
    {
        $this->exporter->shutdown();

        $this->assertFalse($this->exporter->export([]));
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/metrics/sdk_exporters/otlp.md#additional-environment-variable-configuration
     */
    public function test_temporality_preference_selects_by_instrument_type(): void
    {
        $exporter = new ConsoleMetricExporter(TemporalityPreference::delta(), new StreamTransport($this->stream, 'application/json'));
        $metric = $this->createStub(MetricMetadataInterface::class);
        $metric->method('instrumentType')->willReturn(InstrumentType::UP_DOWN_COUNTER);

        $this->assertSame(Temporality::CUMULATIVE, $exporter->temporality($metric));
    }
}
