<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Logs\LogRecordLimits;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LogRecordLimits::class)]
class LogRecordLimitsTest extends TestCase
{
    public function test_get_attribute_factory(): void
    {
        $factory = $this->createMock(AttributesFactoryInterface::class);
        $limits = new LogRecordLimits($factory);

        $this->assertSame($factory, $limits->getAttributeFactory());
    }
}
