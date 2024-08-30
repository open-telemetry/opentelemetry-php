<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Instrumentation;

use OpenTelemetry\API\Instrumentation\SpanAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SpanAttribute::class)]
class SpanAttributeTest extends TestCase
{
    public function test_with_span(): void
    {
        $attr = new SpanAttribute('foo');
        $this->assertSame('foo', $attr->name);
    }
}
