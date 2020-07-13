<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Sdk\Trace\SpanOptions;
use OpenTelemetry\Sdk\Trace\Tracer;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use OpenTelemetry\Trace\SpanKind;
use PHPUnit\Framework\TestCase;

class SpanOptionsTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideSpanKinds
     */
    public function testSpanKindIsSetCorrect($kind)
    {
        $options = new SpanOptions($this->getTracer(), 'test');

        $options->setSpanKind($kind);

        $this->assertEquals($kind, $options->toSpan()->getSpanKind());
    }

    /**
     * @test
     */
    public function testExceptionIsThrownIfInvalidKindIsPassed()
    {
        $nonExistentKind = 999;

        $options = new SpanOptions($this->getTracer(), 'test');

        $this->expectException(\InvalidArgumentException::class);

        $options->setSpanKind($nonExistentKind);
    }

    public function provideSpanKinds(): array
    {
        return [
            [
                SpanKind::KIND_INTERNAL,
            ],
            [
                SpanKind::KIND_CLIENT,
            ],
            [
                SpanKind::KIND_SERVER,
            ],
        ];
    }

    protected function getTracer(): Tracer
    {
        $tracerProvider = new TracerProvider();

        return $tracerProvider->getTracer('OpenTelemetry.TracerTest');
    }
}
