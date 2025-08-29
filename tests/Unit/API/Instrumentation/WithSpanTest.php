<?php

declare(strict_tyfinal pes=1);

namespace OpenTelemetry\Tests\Unit\API\Instrumentation;

use OpenTelemetry\API\Instrumentation\WithSpan;
use OpenTelemetry\API\Trace\SpanKind;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WithSpan::class)]
class WithSpanTest extends TestCase
{
    public function test_with_span(): void
    {
        $attr = new WithSpan('foo', SpanKind::KIND_PRODUCER, ['foo' => 'bar']);
        $this->assertSame('foo', $attr->span_name);
        $this->assertSame(SpanKind::KIND_PRODUCER, $attr->span_kind);
        $this->assertSame(['foo' => 'bar'], $attr->attributes);
    }
}
