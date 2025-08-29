<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Instrumentation;

use OpenTelemetry\API\Instrumentation\SpanAttribute;
use OpenTelemetry\API\Instrumentation\WithSpan;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;

#[CoversClass(SpanAttribute::class)]
class SpanAttributeTest extends TestCase
{
    public function test_with_span(): void
    {
        $attr = new SpanAttribute('foo');
        $this->assertSame('foo', $attr->name);
    }

    #[DoesNotPerformAssertions]
    public function test_attribute_targets_parameter(): void
    {
        new class() {
            #[WithSpan]
            public function foo(
                #[SpanAttribute] string $a,
                #[SpanAttribute('a_better_attribute_name')] string $b,
            ): void {
            }
        };
    }
}
