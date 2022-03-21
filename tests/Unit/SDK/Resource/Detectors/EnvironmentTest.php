<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\SDK\Resource\Detectors;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Resource\Detectors\Environment
 */
class EnvironmentTest extends TestCase
{
    use EnvironmentVariables;

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    public function test_environment_default_get_resource(): void
    {
        $resouceDetector = new Detectors\Environment();
        $resource = $resouceDetector->getResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertEmpty($resource->getAttributes());
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }

    public function test_environment_get_resource_with_resource_attributes(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'key_foo=value_foo,key_bar=value_bar');

        $resouceDetector = new Detectors\Environment();
        $resource = $resouceDetector->getResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertSame('value_foo', $resource->getAttributes()->get('key_foo'));
        $this->assertSame('value_bar', $resource->getAttributes()->get('key_bar'));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }

    public function test_environment_get_resource_with_service_name(): void
    {
        $this->setEnvironmentVariable('OTEL_SERVICE_NAME', 'test-service');

        $resouceDetector = new Detectors\Environment();
        $resource = $resouceDetector->getResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertNotEmpty($resource->getAttributes());
        $this->assertSame('test-service', $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }

    public function test_environment_get_resource_with_service_name_from_resource_attributes(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'service.name=test-service');

        $resouceDetector = new Detectors\Environment();
        $resource = $resouceDetector->getResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertNotEmpty($resource->getAttributes());
        $this->assertSame('test-service', $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }

    public function test_environment_get_resource_service_name_precedence_over_resource_attributes(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'service.name=env-test-service');
        $this->setEnvironmentVariable('OTEL_SERVICE_NAME', 'user-test-service');

        $resouceDetector = new Detectors\Environment();
        $resource = $resouceDetector->getResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertNotEmpty($resource->getAttributes());
        $this->assertSame('user-test-service', $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }
}
