<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Resource;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\TestCase;

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
        $resource = ResourceInfo::create($attributes);

        $name = $resource->getAttributes()->get('name');
        $sdkname = $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME);
        $sdklanguage = $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE);
        $sdkversion = $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_VERSION);

        $attributes->setAttribute(ResourceAttributes::TELEMETRY_SDK_NAME, 'opentelemetry');
        $attributes->setAttribute(ResourceAttributes::TELEMETRY_SDK_LANGUAGE, 'php');
        $attributes->setAttribute(ResourceAttributes::TELEMETRY_SDK_VERSION, 'dev');
        $attributes->setAttribute(ResourceAttributes::SERVICE_NAME, 'unknown_service');

        $this->assertEquals($attributes, $resource->getAttributes());
        $this->assertSame('opentelemetry', $sdkname);
        $this->assertSame('php', $sdklanguage);
        $this->assertSame('dev', $sdkversion);
        $this->assertSame('test', $name);
    }

    /**
     * @test
     */
    public function test_default_resource()
    {
        $attributes = new Attributes(
            [
                ResourceAttributes::TELEMETRY_SDK_NAME => 'opentelemetry',
                ResourceAttributes::TELEMETRY_SDK_LANGUAGE => 'php',
                ResourceAttributes::TELEMETRY_SDK_VERSION => 'dev',
                ResourceAttributes::SERVICE_NAME => 'unknown_service',
            ]
        );
        $resource = ResourceInfo::create(new Attributes());
        $sdkname = $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME);
        $sdklanguage = $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE);
        $sdkversion = $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_VERSION);

        $this->assertEquals($attributes, $resource->getAttributes());
        $this->assertEquals('opentelemetry', $sdkname);
        $this->assertEquals('php', $sdklanguage);
        $this->assertEquals('dev', $sdkversion);
    }

    /**
     * @test
     */
    public function test_merge()
    {
        $primary = ResourceInfo::create(new Attributes(['name' => 'primary', 'empty' => '']));
        $secondary = ResourceInfo::create(new Attributes(['version' => '1.0.0', 'empty' => 'value']));
        $result = ResourceInfo::merge($primary, $secondary);

        $name = $result->getAttributes()->get('name');
        $version = $result->getAttributes()->get('version');
        $empty = $result->getAttributes()->get('empty');

        $this->assertCount(7, $result->getAttributes());
        $this->assertEquals('primary', $name);
        $this->assertEquals('1.0.0', $version);
        $this->assertEquals('value', $empty);
    }

    /**
     * @test
     */
    public function test_immutable_create()
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
     * @test
     * @dataProvider environmentResourceProvider
     */
    public function test_resource_from_environment(string $envAttributes, array $userAttributes, array $expected)
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', $envAttributes);
        $resource = ResourceInfo::create(new Attributes($userAttributes));
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

    /**
     * @test
     */
    public function test_resource_service_name_default()
    {
        $resource = ResourceInfo::create(new Attributes([]));
        $this->assertEquals('unknown_service', $resource->getAttributes()->get('service.name'));
    }

    /**
     * @test
     */
    public function test_resource_with_empty_environment_variable()
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', '');
        $this->assertInstanceOf(ResourceInfo::class, ResourceInfo::create(new Attributes([])));
    }

    /**
     * @test
     */
    public function test_resource_with_invalid_environment_variable()
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'foo');
        $this->assertInstanceOf(ResourceInfo::class, ResourceInfo::create(new Attributes([])));
    }

    /**
     * @test
     */
    public function test_resource_from_environment_service_name_takes_precedence_over_resource_attribute()
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'service.name=bar');
        $this->setEnvironmentVariable('OTEL_SERVICE_NAME', 'foo');
        $resource = ResourceInfo::create(new Attributes([]));
        $this->assertEquals('foo', $resource->getAttributes()->get('service.name'));
    }

    /**
     * @test
     */
    public function test_resource_from_environment_resource_attribute_takes_precedence_over_default()
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'service.name=foo');
        $resource = ResourceInfo::create(new Attributes([]));
        $this->assertEquals('foo', $resource->getAttributes()->get('service.name'));
    }
}
