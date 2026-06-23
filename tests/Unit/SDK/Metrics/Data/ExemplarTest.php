<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\Data;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Metrics\Data\Exemplar;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Exemplar::class)]
final class ExemplarTest extends TestCase
{
    public function test_constructor_sets_properties(): void
    {
        $attributes = $this->createMock(AttributesInterface::class);

        $exemplar = new Exemplar(
            index: 0,
            value: 42.5,
            timestamp: 1234567890,
            attributes: $attributes,
            traceId: 'abc123',
            spanId: 'def456',
        );

        $this->assertSame(42.5, $exemplar->value);
        $this->assertSame(1234567890, $exemplar->timestamp);
        $this->assertSame($attributes, $exemplar->attributes);
        $this->assertSame('abc123', $exemplar->traceId);
        $this->assertSame('def456', $exemplar->spanId);
    }

    public function test_constructor_with_nullable_trace_and_span(): void
    {
        $attributes = $this->createMock(AttributesInterface::class);

        $exemplar = new Exemplar(
            index: 1,
            value: 10,
            timestamp: 100,
            attributes: $attributes,
            traceId: null,
            spanId: null,
        );

        $this->assertNull($exemplar->traceId);
        $this->assertNull($exemplar->spanId);
    }

    public function test_constructor_with_string_index(): void
    {
        $attributes = $this->createMock(AttributesInterface::class);

        $exemplar = new Exemplar(
            index: 'key',
            value: 99,
            timestamp: 200,
            attributes: $attributes,
            traceId: null,
            spanId: null,
        );

        $this->assertSame(99, $exemplar->value);
    }

    public function test_group_by_index(): void
    {
        $attributes = $this->createMock(AttributesInterface::class);

        $e1 = new Exemplar(0, 1.0, 100, $attributes, null, null);
        $e2 = new Exemplar(1, 2.0, 200, $attributes, null, null);
        $e3 = new Exemplar(0, 3.0, 300, $attributes, null, null);

        $grouped = Exemplar::groupByIndex([$e1, $e2, $e3]);

        $this->assertCount(2, $grouped);
        $this->assertCount(2, $grouped[0]);
        $this->assertCount(1, $grouped[1]);
        $this->assertSame($e1, $grouped[0][0]);
        $this->assertSame($e3, $grouped[0][1]);
        $this->assertSame($e2, $grouped[1][0]);
    }

    public function test_group_by_index_with_empty_iterable(): void
    {
        $grouped = Exemplar::groupByIndex([]);

        $this->assertSame([], $grouped);
    }
}
