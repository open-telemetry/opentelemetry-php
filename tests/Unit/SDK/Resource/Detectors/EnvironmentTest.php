<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\Detectors\Environment;
use OpenTelemetry\SemConv\ResourceAttributes;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Environment::class)]
class EnvironmentTest extends TestCase
{
    use TestState;

    private Environment $detector;

    public function setUp(): void
    {
        $this->detector = new Environment();
    }

    public function test_environment_default_get_resource(): void
    {
        $resource = $this->detector->getResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertEmpty($resource->getAttributes());
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }

    public function test_environment_get_resource_with_resource_attributes(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'key_foo=value_foo,key_bar=value_bar');

        $resource = $this->detector->getResource();

        $this->assertSame('value_foo', $resource->getAttributes()->get('key_foo'));
        $this->assertSame('value_bar', $resource->getAttributes()->get('key_bar'));
    }

    #[DataProvider('encodedResourceValueProvider')]
    public function test_environment_get_resource_with_encoded_value(string $value, string $expected): void
    {
        $key = 'key';
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', sprintf('%s=%s', $key, $value));

        $resource = $this->detector->getResource();

        $this->assertSame($expected, $resource->getAttributes()->get($key));
    }

    public static function encodedResourceValueProvider(): array
    {
        return [
            ['%28%24foo%29', '($foo)'],
            ['%21%40%23%24%25%5E', '!@#$%^'],
        ];
    }

    public function test_environment_get_resource_with_service_name(): void
    {
        $this->setEnvironmentVariable('OTEL_SERVICE_NAME', 'test-service');

        $resource = $this->detector->getResource();

        $this->assertNotEmpty($resource->getAttributes());
        $this->assertSame('test-service', $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }

    public function test_environment_get_resource_with_service_name_from_resource_attributes(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'service.name=test-service');

        $resource = $this->detector->getResource();

        $this->assertNotEmpty($resource->getAttributes());
        $this->assertSame('test-service', $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }

    public function test_environment_get_resource_service_name_precedence_over_resource_attributes(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'service.name=env-test-service');
        $this->setEnvironmentVariable('OTEL_SERVICE_NAME', 'user-test-service');

        $resource = $this->detector->getResource();

        $this->assertNotEmpty($resource->getAttributes());
        $this->assertSame('user-test-service', $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }
}
