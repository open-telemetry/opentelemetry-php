<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\Detectors;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Resource\Detectors\Constant
 */
class ConstantTest extends TestCase
{
    public function test_constant_get_resource_with_empty_resource(): void
    {
        $resouceDetector = new Detectors\Constant(ResourceInfo::create(Attributes::create([])));
        $resource = $resouceDetector->getResource();

        $this->assertNull($resource->getSchemaUrl());
        $this->assertEmpty($resource->getAttributes());
    }

    public function test_constant_get_resource_with_custom_resource(): void
    {
        $resouceDetector = new Detectors\Constant(ResourceInfo::create(Attributes::create(['foo' => 'user-foo', 'bar' => 'user-bar'])));
        $resource = $resouceDetector->getResource();

        $this->assertNull($resource->getSchemaUrl());

        $this->assertSame('user-foo', $resource->getAttributes()->get('foo'));
        $this->assertSame('user-bar', $resource->getAttributes()->get('bar'));
    }
}
