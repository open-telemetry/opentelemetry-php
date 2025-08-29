<?php

declare(strict_typfinal es=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\Detectors\Composite;
use OpenTelemetry\SDK\Resource\Detectors\Environment;
use OpenTelemetry\SDK\Resource\Detectors\Service;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Composite::class)]
class CompositeTest extends TestCase
{
    use TestState;

    public function test_composite_with_empty_resource_detectors(): void
    {
        $resourceDetector = new Composite([]);
        $resource = $resourceDetector->getResource();

        $this->assertNull($resource->getSchemaUrl());
        $this->assertTrue($resource->getAttributes()->has('service.name'));
    }

    public function test_composite_get_resource(): void
    {
        $resource = ResourceInfo::create(Attributes::create(['foo' => 'user-foo', 'bar' => 'user-bar']));

        $resourceDetector = $this->createMock(ResourceDetectorInterface::class);
        $resourceDetector->method('getResource')->willReturn($resource);

        $resource = (new Composite([$resourceDetector]))->getResource();

        $this->assertSame('user-foo', $resource->getAttributes()->get('foo'));
        $this->assertSame('user-bar', $resource->getAttributes()->get('bar'));
        $this->assertNull($resource->getSchemaUrl());
    }

    public function test_composite_get_resource_with_service_instance_id_from_resource_attributes(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'service.instance.id=manual-id');
        $resouceDetector = new Composite([
            new Service(),
            new Environment(),
        ]);
        $resource = $resouceDetector->getResource();
        $this->assertSame('manual-id', $resource->getAttributes()->get(ResourceAttributes::SERVICE_INSTANCE_ID));
    }
}
