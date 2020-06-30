<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\Contrib\Zipkin\SpanConverter;
use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\Clock;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanContext;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use PHPUnit\Framework\TestCase;

class ZipkinSpanConverterTest extends TestCase
{
    /**
     * @test
     */
    public function shouldConvertASpanToAPayloadForZipkin()
    {
        $tracer = (new TracerProvider())->getTracer('OpenTelemetry.ZipkinTest');

        $timestamp = Clock::get()->timestamp();

        $span = $tracer->startAndActivateSpan('guard.validate');
        $span->setAttribute('service', 'guard');
        $span->setAttribute('boolean', true);
        $span->addEvent('validators.list', $timestamp, new Attributes(['job' => 'stage.updateTime']));
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

        $this->assertCount(2, $row['tags']);
        $this->assertEquals($span->getAttribute('service')->getValue(), $row['tags']['service']);
        $this->assertEquals($span->getAttribute('boolean')->getValue(), $row['tags']['boolean']);

        $this->assertCount(1, $row['annotations']);
        [$annotation] = $row['annotations'];
        $this->assertEquals('validators.list', $annotation['value']);

        [$event] = \iterator_to_array($span->getEvents());
        $this->assertIsInt($annotation['timestamp']);

        // timestamp should be in microseconds
        $this->assertGreaterThan(1e15, $annotation['timestamp']);
    }

    /**
     * @test
     */
    public function durationShouldBeInMicroseconds()
    {
        $span = new Span('duration.test', SpanContext::generate());

        $row = (new SpanConverter('duration.test'))->convert($span);

        $this->assertEquals(
            (int) (($span->getEndTimestamp() - $span->getStartTimestamp()) / 1000),
            $row['duration']
        );
    }
}
