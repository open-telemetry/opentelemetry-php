<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Common\Http\Psr\Message;

use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Http\Psr\Message\FactoryDecoratorTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Http\Psr\Message\FactoryDecoratorTrait
 */
class FactoryDecoratorTraitTest extends TestCase
{
    public function test_get_propagator(): void
    {
        $instance = $this->createImplementation();
        $propagator = $this->createMock(TextMapPropagatorInterface::class);

        $instance->setPropagator($propagator);

        $this->assertSame(
            $propagator,
            $instance->getPropagator()
        );
    }

    private function createImplementation(): object
    {
        return new class() {
            use FactoryDecoratorTrait;
            public function setPropagator(TextMapPropagatorInterface $propagator): void
            {
                $this->propagator = $propagator;
            }
        };
    }
}
