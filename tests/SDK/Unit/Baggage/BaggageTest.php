<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Baggage;

use OpenTelemetry\API\Baggage\Baggage;
use OpenTelemetry\API\Baggage\Entry;
use OpenTelemetry\API\Baggage\Metadata;
use OpenTelemetry\Context\Context;
use PHPUnit\Framework\TestCase;

class BaggageTest extends TestCase
{
    // region contextInteraction

    public function testCurrentEmpty(): void
    {
        $scope = Context::getRoot()->activate();
        $this->assertSame(Baggage::getCurrent(), \OpenTelemetry\API\Baggage\Baggage::getEmpty());
        $scope->close();
    }

    public function testCurrent(): void
    {
        $scope = Context::getRoot()->withContextValue(
            Baggage::getBuilder()->set('foo', 'bar')->build(),
        )->activate();
        $this->assertSame('bar', Baggage::getCurrent()->getValue('foo'));
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
        $baggage = \OpenTelemetry\API\Baggage\Baggage::getEmpty();
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

    // region functionality

    public function testGetValuePresent(): void
    {
        $this->assertSame(10, Baggage::getBuilder()->set('foo', 10)->build()->getValue('foo'));
    }

    public function testGetValueMissing(): void
    {
        $this->assertNull(Baggage::getBuilder()->build()->getValue('foo'));
    }

    public function testGetEntryPresent(): void
    {
        /** @var Entry $entry */
        $entry = Baggage::getBuilder()->set('foo', 10, new Metadata('meta'))->build()->getEntry('foo');
        $this->assertSame(10, $entry->getValue());
        $this->assertSame('meta', $entry->getMetadata()->getValue());
    }

    public function testGetEntryPresentNoMetadata(): void
    {
        /** @var Entry $entry */
        $entry = Baggage::getBuilder()->set('foo', 10)->build()->getEntry('foo');
        $this->assertSame(10, $entry->getValue());
        $this->assertEmpty($entry->getMetadata()->getValue());
    }

    public function testGetEntryMissing(): void
    {
        $this->assertNull(Baggage::getBuilder()->build()->getEntry('foo'));
    }

    public function testToBuilder(): void
    {
        $baggage = \OpenTelemetry\API\Baggage\Baggage::getBuilder()->set('foo', 10)->build();
        $baggage2 = $baggage->toBuilder()->build();

        $this->assertSame(10, $baggage2->getValue('foo'));
    }

    public function testGetAll(): void
    {
        $baggage = Baggage::getBuilder()
            ->set('foo', 'bar')
            ->set('bar', 'baz')
            ->set('biz', 'fiz')
            ->remove('biz')
            ->build();

        $arr = [];

        foreach ($baggage->getAll() as $key => $value) {
            $arr[$key] = $value->getValue();
        }

        $this->assertEquals(
            ['foo' => 'bar', 'bar' => 'baz'],
            $arr
        );
    }

    // endregion
}
