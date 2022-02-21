<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Composer\InstalledVersions;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\Resource\Detectors;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Resource\ResourceInfo
 */
class ResourceTest extends TestCase
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

    public function test_default_resource(): void
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
                ['foo' => 'foo'],
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

    public function test_composer_detector(): void
    {
        $resource = (new Detectors\Composer())->getResource();

        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::SERVICE_VERSION));
    }
}
