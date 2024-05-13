<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\SDK\Resource\Detectors;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Resource\Detectors\Service
 */
class ServiceTest extends TestCase
{
    use EnvironmentVariables;

    const UUID_REGEX = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    private Detectors\Service $detector;

    public function setUp(): void
    {
        $this->detector = new Detectors\Service();
    }

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    public function test_service_get_resource_with_default_service_instance_id(): void
    {
        $resource = $this->detector->getResource();

        $this->assertNotEmpty($resource->getAttributes());

        $id = $resource->getAttributes()->get(ResourceAttributes::SERVICE_INSTANCE_ID);
        $this->assertMatchesRegularExpression(self::UUID_REGEX, $id);
    }
}
