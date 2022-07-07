<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics\AttributeProcessor;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\AttributeProcessor;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Metrics\AttributeProcessor\Identity
 */
final class IdentityAttributeProcessorTest extends TestCase
{
    public function test_attribute_processor_test(): void
    {
        $this->assertEquals(
            ['foo' => 3, 'bar' => 5],
            (new AttributeProcessor\Identity())
                ->process(Attributes::create(['foo' => 3, 'bar' => 5]), Context::getRoot())
                ->toArray(),
        );
    }
}
