<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Trace\SpanLimits;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SpanLimits::class)]
class SpanLimitsTest extends TestCase
{
    public function test_get_attributes_factory(): void
    {
        $attrFactory = Attributes::factory(128);
        $eventFactory = Attributes::factory(64);
        $linkFactory = Attributes::factory(32);
        $limits = new SpanLimits($attrFactory, $eventFactory, $linkFactory, 128, 128);
        $this->assertSame($attrFactory, $limits->getAttributesFactory());
    }

    public function test_get_event_attributes_factory(): void
    {
        $attrFactory = Attributes::factory(128);
        $eventFactory = Attributes::factory(64);
        $linkFactory = Attributes::factory(32);
        $limits = new SpanLimits($attrFactory, $eventFactory, $linkFactory, 128, 128);
        $this->assertSame($eventFactory, $limits->getEventAttributesFactory());
    }

    public function test_get_link_attributes_factory(): void
    {
        $attrFactory = Attributes::factory(128);
        $eventFactory = Attributes::factory(64);
        $linkFactory = Attributes::factory(32);
        $limits = new SpanLimits($attrFactory, $eventFactory, $linkFactory, 128, 128);
        $this->assertSame($linkFactory, $limits->getLinkAttributesFactory());
    }

    public function test_get_event_count_limit(): void
    {
        $limits = new SpanLimits(Attributes::factory(), Attributes::factory(), Attributes::factory(), 50, 64);
        $this->assertSame(50, $limits->getEventCountLimit());
    }

    public function test_get_link_count_limit(): void
    {
        $limits = new SpanLimits(Attributes::factory(), Attributes::factory(), Attributes::factory(), 128, 64);
        $this->assertSame(64, $limits->getLinkCountLimit());
    }
}
