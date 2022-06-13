<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\SDK\Trace\SpanLimits;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Trace\SpanLimits
 */
class SpanLimitsTest extends TestCase
{
    public function test_span_limits(): void
    {
        $spanLimits = new SpanLimits(1, 2, 3, 4, 5, 6);
        $this->assertSame(1, $spanLimits->getAttributeLimits()->getAttributeCountLimit());
        $this->assertSame(2, $spanLimits->getAttributeLimits()->getAttributeValueLengthLimit());
        $this->assertSame(3, $spanLimits->getEventCountLimit());
        $this->assertSame(4, $spanLimits->getLinkCountLimit());
        $this->assertSame(5, $spanLimits->getAttributePerEventCountLimit());
        $this->assertSame(6, $spanLimits->getAttributePerLinkCountLimit());
    }
}
