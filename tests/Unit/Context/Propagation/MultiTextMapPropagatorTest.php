<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Context\Unit\Propagation;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Propagation\MultiTextMapPropagator;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

class MultiTextMapPropagatorTest extends MockeryTestCase
{
    /** @var Mockery\MockInterface&TextMapPropagatorInterface */
    private $propagator1;

    /** @var Mockery\MockInterface&TextMapPropagatorInterface */
    private $propagator2;

    /** @var Mockery\MockInterface&TextMapPropagatorInterface */
    private $propagator3;

    protected function setUp(): void
    {
        $this->propagator1 = Mockery::mock(TextMapPropagatorInterface::class);
        $this->propagator2 = Mockery::mock(TextMapPropagatorInterface::class);
        $this->propagator3 = Mockery::mock(TextMapPropagatorInterface::class);

        $this->propagator1->shouldReceive('fields')->andReturn([])->byDefault();
        $this->propagator2->shouldReceive('fields')->andReturn([])->byDefault();
        $this->propagator3->shouldReceive('fields')->andReturn([])->byDefault();
    }

    public function test_fields(): void
    {
        $this->propagator1->allows(['fields' => ['foo', 'bar']]);
        $this->propagator2->allows(['fields' => ['hello', 'world']]);

        $this->assertSame(
            ['foo', 'bar', 'hello', 'world'],
            (new MultiTextMapPropagator([$this->propagator1, $this->propagator2]))->fields()
        );
    }

    public function test_fields_duplicates(): void
    {
        $this->propagator1->allows(['fields' => ['foo', 'bar', 'foo']]);
        $this->propagator2->allows(['fields' => ['hello', 'world', 'world', 'bar']]);

        $this->assertSame(
            ['foo', 'bar', 'hello', 'world'],
            (new MultiTextMapPropagator([$this->propagator1, $this->propagator2]))->fields()
        );
    }

    public function test_inject_delegates(): void
    {
        $carrier = [];
        $context = Context::getRoot();

        $this->propagator1->expects('inject')->with($carrier, null, $context);
        $this->propagator2->expects('inject')->with($carrier, null, $context);
        $this->propagator3->expects('inject')->with($carrier, null, $context);

        (new MultiTextMapPropagator([
            $this->propagator1,
            $this->propagator2,
            $this->propagator3,
        ]))->inject($carrier, null, $context);
    }

    public function test_extract_no_propagators(): void
    {
        $this->assertSame(
            Context::getRoot(),
            (new MultiTextMapPropagator([]))->extract([])
        );
    }

    public function test_extract_found_all(): void
    {
        $carrier = [];

        $context1 = new Context();
        $context2 = new Context();
        $context3 = new Context();
        $expectedContext = new Context();

        $this->propagator1->expects('extract')->with($carrier, null, $context1)->andReturn($context2);
        $this->propagator2->expects('extract')->with($carrier, null, $context2)->andReturn($context3);
        $this->propagator3->expects('extract')->with($carrier, null, $context3)->andReturn($expectedContext);

        $this->assertSame(
            $expectedContext,
            (new MultiTextMapPropagator([
                $this->propagator1,
                $this->propagator2,
                $this->propagator3,
            ]))->extract($carrier, null, $context1)
        );
    }

    public function test_extract_not_found(): void
    {
        $carrier = [];

        $context = new Context();

        $this->propagator1->expects('extract')->with($carrier, null, $context)->andReturn($context);
        $this->propagator2->expects('extract')->with($carrier, null, $context)->andReturn($context);

        $this->assertSame(
            $context,
            (new MultiTextMapPropagator([
                $this->propagator1,
                $this->propagator2,
            ]))->extract($carrier, null, $context)
        );
    }
}
