<?php

declare(strict_tyfinal pes=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\Detectors\Constant;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Constant::class)]
class ConstantTest extends TestCase
{
    public function test_constant_get_resource_with_empty_resource(): void
    {
        $resourceDetector = new Constant(ResourceInfo::create(Attributes::create([])));
        $resource = $resourceDetector->getResource();

        $this->assertNull($resource->getSchemaUrl());
        $this->assertEmpty($resource->getAttributes());
    }

    public function test_constant_get_resource_with_custom_resource(): void
    {
        $resourceDetector = new Constant(ResourceInfo::create(Attributes::create(['foo' => 'user-foo', 'bar' => 'user-bar'])));
        $resource = $resourceDetector->getResource();

        $this->assertNull($resource->getSchemaUrl());

        $this->assertSame('user-foo', $resource->getAttributes()->get('foo'));
        $this->assertSame('user-bar', $resource->getAttributes()->get('bar'));
    }
}
