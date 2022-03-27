<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Composer\InstalledVersions;
use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\Detectors;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Resource\ResourceInfo
 */
class ResourceInfoTest extends TestCase
{
    use EnvironmentVariables;

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    public function test_empty_resource(): void
    {
        $resource = ResourceInfo::emptyResource();
        $this->assertEmpty($resource->getAttributes());
    }

    public function test_get_attributes(): void
    {
        $attributes = new Attributes();
        $attributes->setAttribute('name', 'test');

        $resource = (new Detectors\Composite([
            new Detectors\Constant(ResourceInfo::create($attributes)),
            new Detectors\Sdk(),
            new Detectors\SdkProvided(),
        ]))->getResource();

        $version = InstalledVersions::getVersion('open-telemetry/opentelemetry');

        $name = $resource->getAttributes()->get('name');
        $sdkname = $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME);
        $sdklanguage = $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE);
        $sdkversion = $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_VERSION);

        $attributes->setAttribute(ResourceAttributes::TELEMETRY_SDK_NAME, 'opentelemetry');
        $attributes->setAttribute(ResourceAttributes::TELEMETRY_SDK_LANGUAGE, 'php');
        $attributes->setAttribute(ResourceAttributes::TELEMETRY_SDK_VERSION, $version);
        $attributes->setAttribute(ResourceAttributes::SERVICE_NAME, 'unknown_service');

        $this->assertEquals($attributes, $resource->getAttributes());
        $this->assertSame('opentelemetry', $sdkname);
        $this->assertSame('php', $sdklanguage);
        $this->assertSame($version, $sdkversion);
        $this->assertSame('test', $name);
    }

    public function test_all_default_resources(): void
    {
        $resource = ResourceInfo::defaultResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());

        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::HOST_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::HOST_ARCH));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::OS_TYPE));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::OS_DESCRIPTION));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::OS_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::OS_VERSION));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_PID));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_EXECUTABLE_PATH));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND_ARGS));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_VERSION));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_VERSION));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));

        $this->assertEquals('opentelemetry', $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME));
        $this->assertEquals('php', $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE));
        $this->assertEquals('unknown_service', $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }

    public function test_none_default_resources(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_DETECTORS', 'none');

        $resource = ResourceInfo::defaultResource();

        $this->assertNull($resource->getSchemaUrl());

        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::HOST_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::HOST_ARCH));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_TYPE));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_DESCRIPTION));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_VERSION));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_PID));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_EXECUTABLE_PATH));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND_ARGS));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_VERSION));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_VERSION));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }

    public function test_env_default_resources(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_DETECTORS', 'env');
        $this->setEnvironmentVariable('OTEL_SERVICE_NAME', 'test-service');

        $resource = ResourceInfo::defaultResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());

        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::HOST_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::HOST_ARCH));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_TYPE));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_DESCRIPTION));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_VERSION));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_PID));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_EXECUTABLE_PATH));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND_ARGS));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_VERSION));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_VERSION));

        $this->assertEquals('test-service', $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }

    public function test_os_and_host_default_resources(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_DETECTORS', 'os,host');

        $resource = ResourceInfo::defaultResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());

        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::HOST_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::HOST_ARCH));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::OS_TYPE));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::OS_DESCRIPTION));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::OS_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::OS_VERSION));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_PID));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_EXECUTABLE_PATH));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND_ARGS));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_VERSION));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_VERSION));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }

    public function test_process_and_process_runtime_default_resources(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_DETECTORS', 'process,process_runtime');

        $resource = ResourceInfo::defaultResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());

        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::HOST_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::HOST_ARCH));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_TYPE));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_DESCRIPTION));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_VERSION));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_PID));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_EXECUTABLE_PATH));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND_ARGS));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_VERSION));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_VERSION));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }

    public function test_sdk_and_sdk_provided_default_resources(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_DETECTORS', 'sdk,sdk_provided');

        $resource = ResourceInfo::defaultResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());

        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::HOST_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::HOST_ARCH));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_TYPE));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_DESCRIPTION));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::OS_VERSION));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_PID));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_EXECUTABLE_PATH));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND_ARGS));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_VERSION));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_VERSION));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));

        $this->assertEquals('opentelemetry', $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME));
        $this->assertEquals('php', $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE));
        $this->assertEquals('unknown_service', $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }

    public function test_merge(): void
    {
        $primary = ResourceInfo::create(new Attributes(['name' => 'primary', 'empty' => '']));
        $secondary = ResourceInfo::create(new Attributes(['version' => '1.0.0', 'empty' => 'value']));
        $result = ResourceInfo::merge($primary, $secondary);

        $name = $result->getAttributes()->get('name');
        $version = $result->getAttributes()->get('version');
        $empty = $result->getAttributes()->get('empty');

        $this->assertCount(3, $result->getAttributes());
        $this->assertEquals('primary', $name);
        $this->assertEquals('1.0.0', $version);
        $this->assertEquals('', $empty);
    }

    public function test_immutable_create(): void
    {
        $attributes = new Attributes();
        $attributes->setAttribute('name', 'test');
        $attributes->setAttribute('version', '1.0.0');

        $resource = ResourceInfo::create($attributes);

        $attributes->setAttribute('version', '2.0.0');

        $version = $resource->getAttributes()->get('version');

        $this->assertEquals('1.0.0', $version);
    }

    /**
     * @dataProvider environmentResourceProvider
     */
    public function test_resource_from_environment(string $envAttributes, array $userAttributes, array $expected): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', $envAttributes);
        $resource = (new Detectors\Composite([
            new Detectors\Constant(ResourceInfo::create(new Attributes($userAttributes))),
            new Detectors\Environment(),
        ]))->getResource();
        foreach ($expected as $name => $value) {
            $this->assertSame($value, $resource->getAttributes()->get($name));
        }
    }

    public function environmentResourceProvider()
    {
        return [
            'attributes from env var' => [
                'foo=foo,bar=bar',
                [],
                ['foo' => 'foo', 'bar' => 'bar'],
            ],
            'user attributes have higher priority' => [
                'foo=env-foo,bar=env-bar,baz=env-baz',
                ['foo' => 'user-foo', 'bar' => 'user-bar'],
                ['foo' => 'user-foo', 'bar' => 'user-bar', 'baz' => 'env-baz'],
            ],
        ];
    }

    public function test_resource_service_name_default(): void
    {
        $resource = ResourceInfo::defaultResource();
        $this->assertEquals('unknown_service', $resource->getAttributes()->get('service.name'));
    }

    public function test_resource_with_empty_environment_variable(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', '');
        $this->assertInstanceOf(ResourceInfo::class, ResourceInfo::defaultResource());
    }

    public function test_resource_with_invalid_environment_variable(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'foo');
        $this->expectException(InvalidArgumentException::class);
        $this->assertInstanceOf(ResourceInfo::class, ResourceInfo::defaultResource());
    }

    public function test_resource_from_environment_service_name_takes_precedence_over_resource_attribute(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'service.name=bar');
        $this->setEnvironmentVariable('OTEL_SERVICE_NAME', 'foo');
        $resource = ResourceInfo::defaultResource();
        $this->assertEquals('foo', $resource->getAttributes()->get('service.name'));
    }

    public function test_resource_from_environment_resource_attribute_takes_precedence_over_default(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'service.name=foo');
        $resource = ResourceInfo::defaultResource();
        $this->assertEquals('foo', $resource->getAttributes()->get('service.name'));
    }

    /**
     * @dataProvider sameResourcesProvider
     */
    public function test_serialize_returns_same_output_for_objects_representing_the_same_resource(ResourceInfo $resource1, ResourceInfo $resource2): void
    {
        $this->assertSame($resource1->serialize(), $resource2->serialize());
    }

    public function sameResourcesProvider(): iterable
    {
        yield 'Attribute keys sorted in ascending order vs Attribute keys sorted in descending order' => [
            ResourceInfo::create(new Attributes([
                'a' => 'someValue',
                'b' => 'someValue',
                'c' => 'someValue',
            ])),
            ResourceInfo::create(new Attributes([
                'c' => 'someValue',
                'b' => 'someValue',
                'a' => 'someValue',
            ])),
        ];
    }

    /**
     * @dataProvider differentResourcesProvider
     */
    public function test_serialize_returns_different_output_for_objects_representing_different_resources(ResourceInfo $resource1, ResourceInfo $resource2): void
    {
        $this->assertNotSame($resource1->serialize(), $resource2->serialize());
    }

    public function differentResourcesProvider(): iterable
    {
        yield 'Null schema url vs Some schema url' => [
            ResourceInfo::create(new Attributes(), null),
            ResourceInfo::create(new Attributes(), 'someSchemaUrl'),
        ];
    }

    public function test_serialize_incorporates_all_properties(): void
    {
        $resource = ResourceInfo::emptyResource();
        $properties = (new \ReflectionClass($resource))->getProperties();

        $serializedResource = $resource->serialize();

        foreach ($properties as $property) {
            $this->assertStringContainsString($property->getName(), $serializedResource);
        }
    }
}
