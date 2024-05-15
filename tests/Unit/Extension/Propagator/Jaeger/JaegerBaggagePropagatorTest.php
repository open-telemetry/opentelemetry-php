<?php

declare(strict_types=1);

namespace Extension\Propagator\Jaeger;

use OpenTelemetry\API\Baggage\Baggage;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\Extension\Propagator\Jaeger\JaegerBaggagePropagator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(JaegerBaggagePropagator::class)]
class JaegerBaggagePropagatorTest extends TestCase
{
    private TextMapPropagatorInterface $propagator;

    protected function setUp(): void
    {
        $this->propagator = JaegerBaggagePropagator::getInstance();
    }

    public function test_fields(): void
    {
        $this->assertSame(
            [],
            $this->propagator->fields()
        );
    }

    public function test_inject_empty_baggage(): void
    {
        $carrier = [];
        $this->propagator->inject($carrier);

        $this->assertEmpty($carrier);
    }

    public function test_inject_baggage(): void
    {
        $carrier = [];
        $this->propagator->inject(
            $carrier,
            null,
            Context::getCurrent()->withContextValue(
                Baggage::getBuilder()
                    ->set('foo', 'bar')
                    ->build()
            )
        );

        $this->assertSame(
            ['uberctx-foo' => 'bar'],
            $carrier
        );
    }

    public function test_inject_baggage_encoding(): void
    {
        $carrier = [];
        $this->propagator->inject(
            $carrier,
            null,
            Context::getCurrent()->withContextValue(
                Baggage::getBuilder()
                    ->set('foo', 'foo / bar')
                    ->build()
            )
        );

        $this->assertSame(
            ['uberctx-foo' => 'foo%20%2F%20bar'],
            $carrier
        );
    }

    public function test_extract_empty_baggage(): void
    {
        $this->assertEquals(
            Context::getCurrent(),
            $this->propagator->extract([])
        );
    }

    public function test_extract_baggage(): void
    {
        $carrier = [
            'uberctx-foo' => 'bar',
            'uberctxfoo' => 'bar',
            'another' => 'foo',
        ];

        $context = $this->propagator->extract($carrier);

        $this->assertEquals(
            Baggage::getBuilder()->set('foo', 'bar')->build(),
            Baggage::fromContext($context)
        );
    }
}
