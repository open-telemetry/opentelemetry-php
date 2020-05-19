<?php

declare(strict_types=1);

namespace unit\Exporter;

use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use OpenTelemetry\Sdk\Trace\Zipkin\SpanConverter;
use PHPUnit\Framework\TestCase;

class ZipkinSpanConverterTest extends TestCase
{
    /**
     * @test
     */
    public function shouldConvertASpanToAPayloadForZipkin()
    {
        $tracerProvider = new TracerProvider();
        $tracer = $tracerProvider->getTracer('ZipkinSpanConverterTest');
        $span = $tracer->startAndActivateSpan('guard.validate');
        $span->setAttribute('service', 'guard');
        $span->addEvent('validators.list', new Attributes(['job' => 'stage.updateTime']));
        $span->end();

        $converter = new SpanConverter('test.name');
        $row = $converter->convert($span);

        $this->assertSame($span->getContext()->getSpanId(), $row['id']);
        $this->assertSame($span->getContext()->getTraceId(), $row['traceId']);

        $this->assertSame('test.name', $row['localEndpoint']['serviceName']);
        $this->assertSame($span->getSpanName(), $row['name']);

        $this->assertIsInt($row['timestamp']);
        // timestamp should be in microseconds
        $this->assertGreaterThan(1e15, $row['timestamp']);

        $this->assertIsInt($row['duration']);
        $this->assertGreaterThan(0, $row['duration']);

        $this->assertCount(1, $row['tags']);
        $this->assertEquals($span->getAttribute('service')->getValue(), $row['tags']['service']);

        $this->assertCount(1, $row['annotations']);
        [$annotation] = $row['annotations'];
        $this->assertEquals('validators.list', $annotation['value']);

        [$event] = \iterator_to_array($span->getEvents());
        $this->assertIsInt($annotation['timestamp']);

        // timestamp should be in microseconds
        $this->assertGreaterThan(1e15, $annotation['timestamp']);
    }
}
