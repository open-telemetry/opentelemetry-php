<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Trace;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Sdk\Trace\Attributes;
use OpenTelemetry\Sdk\Trace\Span;
use OpenTelemetry\Sdk\Trace\SpanOptions;
use OpenTelemetry\Tests\Sdk\Unit\Support\HasTraceProvider;
use OpenTelemetry\Trace\SpanKind;
use PHPUnit\Framework\TestCase;

class SpanOptionsTest extends TestCase
{
    use HasTraceProvider;

    public function testShouldCreateSpanFromOptions(): void
    {
        $tracer = $this->getTracer();
        $tracer->startAndActivateSpan('firstSpan');
        $spanOptions = new SpanOptions($tracer, 'web');

        $global = $tracer->getActiveSpan();
        $globalContext = (new Context());
        $globalContext = $globalContext->withContextValue($global);
        $spanOptions->setParent($globalContext);

        // Create span from options
        /** @var Span $web */
        $web = $spanOptions->toSpan();

        // Make sure created span is not activated
        $this->assertSame($tracer->getActiveSpan(), $global);

        $this->assertSame($global->getContext()->getTraceId(), $web->getContext()->getTraceId());
        $this->assertEquals($web->getParent(), $global->getContext());
        $this->assertNotNull($web->getStartEpochTimestamp());
        $this->assertNotNull($web->getStart());
        $this->assertTrue($web->isRecording());
        $this->assertNull($web->getDuration());
    }

    public function testShouldCreateAndSetActiveSpanFromOptions(): void
    {
        $tracer = $this->getTracer();
        $tracer->startAndActivateSpan('firstSpan');
        $spanOptions = new SpanOptions($tracer, 'web');
        $tracer->getActiveSpan();
        $this->assertSame($spanOptions, $spanOptions->setSpanName('web2'));
        $this->assertSame($spanOptions, $spanOptions->addStartTimestamp(1234));

        $web = $spanOptions->toActiveSpan();

        // Make sure span is activated
        $this->assertSame($tracer->getActiveSpan(), $web);

        // Assert previously set vars
        $this->assertEquals('web2', $web->getSpanName());
        $this->assertEquals(1234, $web->getStartEpochTimestamp());
    }

    public function testShouldCreateCorrectSpanAttributes(): void
    {
        $tracer = $this->getTracer();
        $tracer->startAndActivateSpan('firstSpan');
        $spanOptions = new SpanOptions($tracer, 'web');
        $tracer->getActiveSpan();

        $attribRaw = [
            'attr_1' => 'value_1',
            'attr_2' => 2,
            'attr_3' => true,
            'attr_4' => 3.14159,
            'attr_5' => [1,2,3,4,5],
            'attr_6' => [1.1,2.2,3.3,4.4,5.5],
        ];

        $attributes = new Attributes($attribRaw);

        $spanOptions->addAttributes($attributes);

        $web = $spanOptions->toActiveSpan();

        // Check that span attributes are the ones passed in to spanOptions
        $this->assertEquals($attributes, $web->getAttributes());
    }

    /**
     * @dataProvider provideSpanKinds
     */
    public function testSpanKindIsSetCorrect($kind): void
    {
        $options = new SpanOptions($this->getTracer(), 'test');

        $options->setSpanKind($kind);

        $this->assertEquals($kind, $options->toSpan()->getSpanKind());
    }

    public function testExceptionIsThrownIfInvalidKindIsPassed(): void
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
}
