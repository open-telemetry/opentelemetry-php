<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Context\Propagation;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Propagation\MultiResponsePropagator;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MultiResponsePropagator::class)]
class MultiResponsePropagatorTest extends MockeryTestCase
{
    /** @var Mockery\MockInterface&ResponsePropagatorInterface */
    private $propagator1;

    /** @var Mockery\MockInterface&ResponsePropagatorInterface */
    private $propagator2;

    /** @var Mockery\MockInterface&ResponsePropagatorInterface */
    private $propagator3;

    #[\Override]
    protected function setUp(): void
    {
        $this->propagator1 = Mockery::mock(ResponsePropagatorInterface::class);
        $this->propagator2 = Mockery::mock(ResponsePropagatorInterface::class);
        $this->propagator3 = Mockery::mock(ResponsePropagatorInterface::class);
    }

    public function test_inject_delegates(): void
    {
        $carrier = [];
        $context = Context::getRoot();

        $this->propagator1->expects('inject')->with($carrier, null, $context);
        $this->propagator2->expects('inject')->with($carrier, null, $context);
        $this->propagator3->expects('inject')->with($carrier, null, $context);

        (new MultiResponsePropagator([
            $this->propagator1,
            $this->propagator2,
            $this->propagator3,
        ]))->inject($carrier, null, $context);
    }
}
