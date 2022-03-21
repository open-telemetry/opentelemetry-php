<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\Detectors;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Resource\Detectors\OperatingSystem
 */
class OperatingSystemTest extends TestCase
{
    public function test_operating_system_get_resource(): void
    {
        $resouceDetector = new Detectors\OperatingSystem();
        $resource = $resouceDetector->getResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::OS_TYPE));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::OS_DESCRIPTION));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::OS_NAME));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::OS_VERSION));
    }
}
