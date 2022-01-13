<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use OpenTelemetry\SDK\AttributeLimits;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\AttributeLimits
 */
class AttributeLimitsTest extends TestCase
{
    public function test_default_limits(): void
    {
        $limits = new AttributeLimits();
        $this->assertNotNull($limits->getAttributeCountLimit());
        $this->assertNotNull($limits->getAttributeValueLengthLimit());
    }

    public function test_limits(): void
    {
        $limits = new AttributeLimits(10, 20);
        $this->assertSame(10, $limits->getAttributeCountLimit());
        $this->assertSame(20, $limits->getAttributeValueLengthLimit());
    }
}
