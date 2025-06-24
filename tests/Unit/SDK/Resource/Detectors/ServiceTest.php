<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\Detectors;
use OpenTelemetry\SemConv\ResourceAttributes;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Detectors\Service::class)]
class ServiceTest extends TestCase
{
    use TestState;

    private Detectors\Service $detector;

    public function setUp(): void
    {
        $this->detector = new Detectors\Service();
    }

    public function test_sdk_get_resource_with_service_name(): void
    {
        $this->setEnvironmentVariable('OTEL_SERVICE_NAME', 'test-service');

        $resource = $this->detector->getResource();

        $this->assertNotEmpty($resource->getAttributes());
        $this->assertSame('test-service', $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }
}
