<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Context\Propagation;

use OpenTelemetry\Context\Propagation\ExtendedPropagationGetterInterface;
use OpenTelemetry\Context\Propagation\PropagationGetterInterface;
use OpenTelemetry\Context\Propagation\SanitizeCombinedHeadersPropagationGetter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SanitizeCombinedHeadersPropagationGetter::class)]
class SanitizeCombinedHeadersPropagationGetterTest extends TestCase
{
    public function test_get_returns_null_when_inner_returns_null(): void
    {
        $inner = $this->createMock(PropagationGetterInterface::class);
        $inner->method('get')->willReturn(null);

        $getter = new SanitizeCombinedHeadersPropagationGetter($inner);

        $this->assertNull($getter->get([], 'traceparent'));
    }

    public function test_get_replaces_semicolons_between_key_value_pairs_with_commas(): void
    {
        $inner = $this->createMock(PropagationGetterInterface::class);
        $inner->method('get')->willReturn('key1=value1;key2=value2');

        $getter = new SanitizeCombinedHeadersPropagationGetter($inner);

        $this->assertSame('key1=value1,key2=value2', $getter->get([], 'tracestate'));
    }

    public function test_get_trims_trailing_and_leading_commas(): void
    {
        $inner = $this->createMock(PropagationGetterInterface::class);
        $inner->method('get')->willReturn(',,key1=value1,key2=value2,,');

        $getter = new SanitizeCombinedHeadersPropagationGetter($inner);

        $this->assertSame('key1=value1,key2=value2', $getter->get([], 'tracestate'));
    }

    public function test_keys_delegates_to_inner_getter(): void
    {
        $inner = $this->createMock(PropagationGetterInterface::class);
        $inner->method('keys')->willReturn(['traceparent', 'tracestate']);

        $getter = new SanitizeCombinedHeadersPropagationGetter($inner);

        $this->assertSame(['traceparent', 'tracestate'], $getter->keys([]));
    }

    public function test_get_all_uses_get_all_when_inner_implements_extended_getter(): void
    {
        $inner = $this->createMock(ExtendedPropagationGetterInterface::class);
        $inner->method('getAll')->willReturn(['key1=value1;key2=value2', 'key3=value3']);

        $getter = new SanitizeCombinedHeadersPropagationGetter($inner);

        $this->assertSame(['key1=value1,key2=value2', 'key3=value3'], $getter->getAll([], 'tracestate'));
    }

    public function test_get_all_falls_back_to_get_when_inner_does_not_implement_extended_getter(): void
    {
        $inner = $this->createMock(PropagationGetterInterface::class);
        $inner->method('get')->willReturn('key1=value1;key2=value2');

        $getter = new SanitizeCombinedHeadersPropagationGetter($inner);

        $this->assertSame(['key1=value1,key2=value2'], $getter->getAll([], 'tracestate'));
    }

    public function test_get_all_returns_empty_array_when_value_is_empty(): void
    {
        $inner = $this->createMock(ExtendedPropagationGetterInterface::class);
        $inner->method('getAll')->willReturn([]);

        $getter = new SanitizeCombinedHeadersPropagationGetter($inner);

        $this->assertSame([], $getter->getAll([], 'tracestate'));
    }
}
