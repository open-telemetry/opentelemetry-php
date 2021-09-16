<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Baggage;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Sdk\Baggage\Baggage;
use PHPUnit\Framework\TestCase;

class BaggageTest extends TestCase
{
    // region contextInteraction

    public function testCurrentEmpty(): void
    {
        $scope = Context::getRoot()->activate();
        $this->assertSame(Baggage::getCurrent(), Baggage::getEmpty());
        $scope->close();
    }

    public function testCurrent(): void
    {
        $scope = Context::getRoot()->withContextValue(
            (new Baggage())->set('foo', 'bar') // TODO: Replace this wither the builder
        )->activate();
        $result = Baggage::getCurrent();
        $this->assertSame('bar', $result->getValue('foo'));
        $scope->close();
    }

    public function testGetCurrentBaggageDefault(): void
    {
        $scope = Context::getRoot()->activate();
        $baggage = Baggage::getCurrent();
        $this->assertSame($baggage, Baggage::getEmpty());
        $scope->close();
    }

    public function testGetCurrentBaggageSetsCorrectContext(): void
    {
        $baggage = Baggage::getEmpty();
        $scope = Context::getRoot()->withContextValue($baggage)->activate();
        $this->assertSame(Baggage::getCurrent(), $baggage);
        $scope->close();
    }

    public function testBaggageFromContextDefaultContext(): void
    {
        $baggage = Baggage::fromContext(Context::getRoot());
        $this->assertSame($baggage, Baggage::getEmpty());
    }

    public function testGetBaggageExplicitContext(): void
    {
        $baggage = Baggage::getEmpty();
        $context = Context::getRoot()->withContextValue($baggage);
        $this->assertSame(Baggage::fromContext($context), $baggage);
    }

    // endregion
}
