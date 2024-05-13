<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\SemConv\ResourceAttributes;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\Detectors;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Resource\Detectors\Composite
 */
class CompositeTest extends TestCase
{
    use EnvironmentVariables;

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    public function test_composite_with_empty_resource_detectors(): void
    {
        $resouceDetector = new Detectors\Composite([]);
        $resource = $resouceDetector->getResource();

        $this->assertNull($resource->getSchemaUrl());
        $this->assertEmpty($resource->getAttributes());
    }

    public function test_composite_get_resource(): void
    {
        $resource = ResourceInfo::create(Attributes::create(['foo' => 'user-foo', 'bar' => 'user-bar']));

        $resourceDetector = $this->createMock(ResourceDetectorInterface::class);
        $resourceDetector->method('getResource')->willReturn($resource);

        $resource = (new Detectors\Composite([$resourceDetector]))->getResource();

        $this->assertSame('user-foo', $resource->getAttributes()->get('foo'));
        $this->assertSame('user-bar', $resource->getAttributes()->get('bar'));
        $this->assertNull($resource->getSchemaUrl());
    }

    public function test_composite_get_resource_with_service_instance_id_from_resource_attributes()
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'service.instance.id=manual-id');
        $resouceDetector = new Detectors\Composite([
            new Detectors\Service(),
            new Detectors\Environment(),
        ]);
        $resource = $resouceDetector->getResource();
        $this->assertSame('manual-id', $resource->getAttributes()->get(ResourceAttributes::SERVICE_INSTANCE_ID));
    }
}
