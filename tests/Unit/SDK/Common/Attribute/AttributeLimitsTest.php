<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Attribute;

use OpenTelemetry\SDK\Common\Attribute\AttributeLimits;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Attribute\AttributeLimits
 */
class AttributeLimitsTest extends TestCase
{
    public function test_compare(): void
    {
        $attrLimits1 = new AttributeLimits(10, 20);
        $attrLimits2 = new AttributeLimits(10, 20);
        $attrLimits3 = new AttributeLimits(20, 30);

        $this->assertEquals($attrLimits1, $attrLimits2);
        $this->assertNotEquals($attrLimits1, $attrLimits3);
    }

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
