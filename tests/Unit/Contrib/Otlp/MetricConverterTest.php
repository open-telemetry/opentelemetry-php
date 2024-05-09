<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Otlp;

use OpenTelemetry\Contrib\Otlp\MetricConverter;
use Opentelemetry\Proto\Collector\Metrics\V1\ExportMetricsServiceRequest;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Metrics\Data\DataInterface;
use OpenTelemetry\SDK\Metrics\Data\Exemplar;
use OpenTelemetry\SDK\Metrics\Data\Gauge;
use OpenTelemetry\SDK\Metrics\Data\Histogram;
use OpenTelemetry\SDK\Metrics\Data\HistogramDataPoint;
use OpenTelemetry\SDK\Metrics\Data\Metric;
use OpenTelemetry\SDK\Metrics\Data\NumberDataPoint;
use OpenTelemetry\SDK\Metrics\Data\Sum;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\Contrib\Otlp\MetricConverter::class)]
final class MetricConverterTest extends TestCase
{
    public function test_empty_batch_returns_empty_request(): void
    {
        $this->assertEquals(
            new ExportMetricsServiceRequest(),
            (new MetricConverter())->convert([]),
        );
    }

    public function test_metrics_are_converted_to_metrics_request(): void
    {
        $this->assertJsonStringEqualsJsonString(
            <<<JSON
                {"resourceMetrics":[{"resource":{"attributes":[{"key":"a","value":{"stringValue":"b"}}]},"scopeMetrics":[{"scope":{"name":"test"},"metrics":[
                    {"name":"test-1","sum":{"dataPoints":[
                        {"attributes":[{"key":"foo","value":{"stringValue":"bar"}}],"startTimeUnixNano":"17","timeUnixNano":"42","asInt":"5"}
                    ],"aggregationTemporality":"AGGREGATION_TEMPORALITY_CUMULATIVE"}},
                    {"name":"test-2","histogram":{"dataPoints":[
                        {"startTimeUnixNano":"17","timeUnixNano":"42","count":"2","sum":7,"bucketCounts":["2", "0"],"explicitBounds":[5]}
                    ],"aggregationTemporality":"AGGREGATION_TEMPORALITY_DELTA"}},
                    {"name":"test-3","gauge":{"dataPoints":[
                        {"startTimeUnixNano":"17","timeUnixNano":"42","asDouble":9.5}
                    ]}}
                ]}]}]}
                JSON,
            (new MetricConverter())->convert([
                new Metric(
                    new InstrumentationScope('test', null, null, Attributes::create([])),
                    ResourceInfo::create(Attributes::create(['a' => 'b'])),
                    'test-1',
                    null,
                    null,
                    new Sum([
                        new NumberDataPoint(5, Attributes::create(['foo' => 'bar']), 17, 42),
                    ], Temporality::CUMULATIVE, false)
                ),
                new Metric(
                    new InstrumentationScope('test', null, null, Attributes::create([])),
                    ResourceInfo::create(Attributes::create(['a' => 'b'])),
                    'test-2',
                    null,
                    null,
                    new Histogram([
                        new HistogramDataPoint(2, 7, 3, 4, [2, 0], [5], Attributes::create([]), 17, 42),
                    ], Temporality::DELTA)
                ),
                new Metric(
                    new InstrumentationScope('test', null, null, Attributes::create([])),
                    ResourceInfo::create(Attributes::create(['a' => 'b'])),
                    'test-3',
                    null,
                    null,
                    new Gauge([
                        new NumberDataPoint(9.5, Attributes::create([]), 17, 42),
                    ])
                ),
            ])->serializeToJsonString(),
        );
    }

    public function test_sum_exemplars_are_converted(): void
    {
        $this->assertJsonStringEqualsJsonString(
            <<<JSON
                {"resourceMetrics":[{"resource":{},"scopeMetrics":[{"scope":{"name":"test"},"metrics":[
                    {"name":"test-1","sum":{"dataPoints":[
                        {"attributes":[{"key":"foo","value":{"stringValue":"bar"}}],"startTimeUnixNano":"17","timeUnixNano":"42","asInt":"5","exemplars":[
                            {"filteredAttributes":[{"key":"key","value":{"stringValue":"value"}}],"timeUnixNano":"19","asDouble":0.5},
                            {"filteredAttributes":[{"key":"key","value":{"stringValue":"other"}}],"timeUnixNano":"37","asInt":"-3","spanId":"APBnqgupArc=","traceId":"S/kvNXezTaajzpKdDg5HNg=="}
                        ]}
                    ],"aggregationTemporality":"AGGREGATION_TEMPORALITY_DELTA"}}
                ]}]}]}
                JSON,
            (new MetricConverter())->convert([
                new Metric(
                    new InstrumentationScope('test', null, null, Attributes::create([])),
                    ResourceInfoFactory::emptyResource(),
                    'test-1',
                    null,
                    null,
                    new Sum([
                        new NumberDataPoint(5, Attributes::create(['foo' => 'bar']), 17, 42, [
                            new Exemplar(0, .5, 19, Attributes::create(['key' => 'value']), null, null),
                            new Exemplar(0, -3, 37, Attributes::create(['key' => 'other']), '4bf92f3577b34da6a3ce929d0e0e4736', '00f067aa0ba902b7'),
                        ]),
                    ], Temporality::DELTA, false)
                ),
            ])->serializeToJsonString(),
        );
    }

    public function test_histogram_exemplars_are_converted(): void
    {
        $this->assertJsonStringEqualsJsonString(
            <<<JSON
                {"resourceMetrics":[{"resource":{},"scopeMetrics":[{"scope":{"name":"test"},"metrics":[
                    {"name":"test-1","histogram":{"dataPoints":[
                        {"attributes":[{"key":"foo","value":{"stringValue":"bar"}}],"startTimeUnixNano":"17","timeUnixNano":"42","count":"5","sum":9,"bucketCounts":["5"],"exemplars":[
                            {"filteredAttributes":[{"key":"key","value":{"stringValue":"value"}}],"timeUnixNano":"19","asDouble":0.5},
                            {"filteredAttributes":[{"key":"key","value":{"stringValue":"other"}}],"timeUnixNano":"37","asInt":"-3","spanId":"APBnqgupArc=","traceId":"S/kvNXezTaajzpKdDg5HNg=="}
                        ]}
                    ],"aggregationTemporality":"AGGREGATION_TEMPORALITY_DELTA"}}
                ]}]}]}
                JSON,
            (new MetricConverter())->convert([
                new Metric(
                    new InstrumentationScope('test', null, null, Attributes::create([])),
                    ResourceInfoFactory::emptyResource(),
                    'test-1',
                    null,
                    null,
                    new Histogram([
                        new HistogramDataPoint(5, 9, -2, 8, [5], [], Attributes::create(['foo' => 'bar']), 17, 42, [
                            new Exemplar(0, .5, 19, Attributes::create(['key' => 'value']), null, null),
                            new Exemplar(0, -3, 37, Attributes::create(['key' => 'other']), '4bf92f3577b34da6a3ce929d0e0e4736', '00f067aa0ba902b7'),
                        ]),
                    ], Temporality::DELTA)
                ),
            ])->serializeToJsonString(),
        );
    }

    public function test_gauge_exemplars_are_converted(): void
    {
        $this->assertJsonStringEqualsJsonString(
            <<<JSON
                {"resourceMetrics":[{"resource":{},"scopeMetrics":[{"scope":{"name":"test"},"metrics":[
                    {"name":"test-1","gauge":{"dataPoints":[
                        {"attributes":[{"key":"foo","value":{"stringValue":"bar"}}],"startTimeUnixNano":"17","timeUnixNano":"42","asInt":"5","exemplars":[
                            {"filteredAttributes":[{"key":"key","value":{"stringValue":"value"}}],"timeUnixNano":"19","asDouble":0.5},
                            {"filteredAttributes":[{"key":"key","value":{"stringValue":"other"}}],"timeUnixNano":"37","asInt":"-3","spanId":"APBnqgupArc=","traceId":"S/kvNXezTaajzpKdDg5HNg=="}
                        ]}
                    ]}}
                ]}]}]}
                JSON,
            (new MetricConverter())->convert([
                new Metric(
                    new InstrumentationScope('test', null, null, Attributes::create([])),
                    ResourceInfoFactory::emptyResource(),
                    'test-1',
                    null,
                    null,
                    new Gauge([
                        new NumberDataPoint(5, Attributes::create(['foo' => 'bar']), 17, 42, [
                            new Exemplar(0, .5, 19, Attributes::create(['key' => 'value']), null, null),
                            new Exemplar(0, -3, 37, Attributes::create(['key' => 'other']), '4bf92f3577b34da6a3ce929d0e0e4736', '00f067aa0ba902b7'),
                        ]),
                    ])
                ),
            ])->serializeToJsonString(),
        );
    }

    public function test_multiple_resources_result_in_multiple_resource_metrics(): void
    {
        $resourceA = ResourceInfo::create(Attributes::create(['foo' => 'bar']));
        $resourceB = ResourceInfo::create(Attributes::create(['foo' => 'baz']));
        $this->assertCount(
            2,
            (new MetricConverter())->convert([
                new Metric(
                    $this->createMock(InstrumentationScopeInterface::class),
                    $resourceA,
                    'test',
                    null,
                    null,
                    $this->createMock(DataInterface::class),
                ),
                new Metric(
                    $this->createMock(InstrumentationScopeInterface::class),
                    $resourceB,
                    'test',
                    null,
                    null,
                    $this->createMock(DataInterface::class),
                ),
            ])->getResourceMetrics(),
        );
    }

    public function test_instrumentation_scope_is_converted(): void
    {
        $this->assertJsonStringEqualsJsonString(
            <<<JSON
                {"resourceMetrics": [{"resource": {},"scopeMetrics": [{
                    "metrics": [{"description": "description-1","name": "name-1","unit": "unit-1"}],
                    "schemaUrl": "http://schema.url",
                    "scope": {"attributes": [{"key": "foo","value": {"stringValue": "bar"}}],"name": "scope-name","version": "scope-version"}
                }
            ]}]}
            JSON,
            (new MetricConverter())->convert([
                new Metric(
                    new InstrumentationScope('scope-name', 'scope-version', 'http://schema.url', Attributes::create(['foo' => 'bar'])),
                    ResourceInfoFactory::emptyResource(),
                    'name-1',
                    'unit-1',
                    'description-1',
                    $this->createMock(DataInterface::class),
                ),
            ])->serializeToJsonString(),
        );
    }
}
