<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\Detectors;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Resource\Detectors\Host
 */
class HostTest extends TestCase
{
    public function test_host_get_resource(): void
    {
        $resouceDetector = new Detectors\Host();
        $resource = $resouceDetector->getResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::HOST_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::HOST_ARCH));
    }
}
