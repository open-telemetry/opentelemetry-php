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

    const UUID_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    private Detectors\Service $detector;

    #[\Override]
    public function setUp(): void
    {
        $this->detector = new Detectors\Service();
    }

    public function test_service_get_resource_with_default_service_instance_id(): void
    {
        $resource = $this->detector->getResource();

        $this->assertNotEmpty($resource->getAttributes());

        $id = $resource->getAttributes()->get(ResourceAttributes::SERVICE_INSTANCE_ID);
        $this->assertMatchesRegularExpression(self::UUID_REGEX, $id);
    }

    public function test_service_get_resource_multiple_calls_same_service_instance_id(): void
    {
        $resource1 = $this->detector->getResource();
        $resource2 = $this->detector->getResource();

        $this->assertSame($resource1->getAttributes()->get(ResourceAttributes::SERVICE_INSTANCE_ID), $resource2->getAttributes()->get(ResourceAttributes::SERVICE_INSTANCE_ID));
    }

    public function test_sdk_get_resource_with_service_name(): void
    {
        $this->setEnvironmentVariable('OTEL_SERVICE_NAME', 'test-service');

        $resource = $this->detector->getResource();

        $this->assertNotEmpty($resource->getAttributes());
        $this->assertSame('test-service', $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }
}
