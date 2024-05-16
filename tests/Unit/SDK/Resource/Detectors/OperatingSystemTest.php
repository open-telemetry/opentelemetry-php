<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\Detectors\OperatingSystem;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OperatingSystem::class)]
class OperatingSystemTest extends TestCase
{
    public function test_operating_system_get_resource(): void
    {
        $resourceDetector = new OperatingSystem();
        $resource = $resourceDetector->getResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::OS_TYPE));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::OS_DESCRIPTION));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::OS_NAME));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::OS_VERSION));
    }
}
