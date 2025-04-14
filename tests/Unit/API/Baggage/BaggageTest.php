<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\API\Unit\Baggage;

use OpenTelemetry\API\Baggage\Baggage;
use OpenTelemetry\API\Baggage\Entry;
use OpenTelemetry\API\Baggage\Metadata;
use OpenTelemetry\Context\Context;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Baggage::class)]
class BaggageTest extends TestCase
{
    // region contextInteraction

    public function test_current_empty(): void
    {
        $scope = Context::getRoot()->activate();
        $this->assertSame(Baggage::getCurrent(), Baggage::getEmpty());
        $scope->detach();
    }

    public function test_current(): void
    {
        $scope = Context::getRoot()->withContextValue(
            Baggage::getBuilder()->set('foo', 'bar')->build(),
        )->activate();
        $this->assertSame('bar', Baggage::getCurrent()->getValue('foo'));
        $scope->detach();
    }

    public function test_get_current_baggage_default(): void
    {
        $scope = Context::getRoot()->activate();
        $baggage = Baggage::getCurrent();
        $this->assertSame($baggage, Baggage::getEmpty());
        $scope->detach();
    }

    public function test_get_current_baggage_sets_correct_context(): void
    {
        $baggage = Baggage::getEmpty();
        $scope = Context::getRoot()->withContextValue($baggage)->activate();
        $this->assertSame(Baggage::getCurrent(), $baggage);
        $scope->detach();
    }

    public function test_baggage_from_context_default_context(): void
    {
        $baggage = Baggage::fromContext(Context::getRoot());
        $this->assertSame($baggage, Baggage::getEmpty());
    }

    public function test_get_baggage_explicit_context(): void
    {
        $baggage = Baggage::getEmpty();
        $context = Context::getRoot()->withContextValue($baggage);
        $this->assertSame(Baggage::fromContext($context), $baggage);
    }

    // endregion

    // region functionality

    public function test_get_value_present(): void
    {
        $this->assertSame(10, Baggage::getBuilder()->set('foo', 10)->build()->getValue('foo'));
    }

    public function test_get_value_missing(): void
    {
        $this->assertNull(Baggage::getBuilder()->build()->getValue('foo'));
    }

    public function test_get_entry_present(): void
    {
        /** @var Entry $entry */
        $entry = Baggage::getBuilder()->set('foo', 10, new Metadata('meta'))->build()->getEntry('foo');
        $this->assertSame(10, $entry->getValue());
        $this->assertSame('meta', $entry->getMetadata()->getValue());
    }

    public function test_get_entry_present_no_metadata(): void
    {
        /** @var Entry $entry */
        $entry = Baggage::getBuilder()->set('foo', 10)->build()->getEntry('foo');
        $this->assertSame(10, $entry->getValue());
        $this->assertEmpty($entry->getMetadata()->getValue());
    }

    public function test_get_entry_missing(): void
    {
        $this->assertNull(Baggage::getBuilder()->build()->getEntry('foo'));
    }

    public function test_to_builder(): void
    {
        $baggage = Baggage::getBuilder()->set('foo', 10)->build();
        $baggage2 = $baggage->toBuilder()->build();

        $this->assertSame(10, $baggage2->getValue('foo'));
    }

    public function test_get_all(): void
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

    public function test_empty_name_disallowed(): void
    {
        $baggage = Baggage::getBuilder()->set('', 'bar')->build();
        $this->assertTrue($baggage->isEmpty());
    }

    // endregion
}
