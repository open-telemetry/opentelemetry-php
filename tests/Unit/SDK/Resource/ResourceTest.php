<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource;

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
        $resource = ResourceInfo::create(Attributes::create(['name' => 'test']));

        $name = $resource->getAttributes()->get('name');
        $sdkname = $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME);
        $sdklanguage = $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE);
        $sdkversion = $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_VERSION);

        $this->assertSame('opentelemetry', $sdkname);
        $this->assertSame('php', $sdklanguage);
        $this->assertSame('dev', $sdkversion);
        $this->assertSame('test', $name);
    }

    public function test_default_resource(): void
    {
        $attributes = Attributes::create(
            [
                ResourceAttributes::TELEMETRY_SDK_NAME => 'opentelemetry',
                ResourceAttributes::TELEMETRY_SDK_LANGUAGE => 'php',
                ResourceAttributes::TELEMETRY_SDK_VERSION => 'dev',
                ResourceAttributes::SERVICE_NAME => 'unknown_service',
            ]
        );
        $resource = ResourceInfo::create(Attributes::create());
        $sdkname = $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME);
        $sdklanguage = $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE);
        $sdkversion = $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_VERSION);

        $this->assertEquals($attributes, $resource->getAttributes());
        $this->assertEquals('opentelemetry', $sdkname);
        $this->assertEquals('php', $sdklanguage);
        $this->assertEquals('dev', $sdkversion);
    }

    public function test_merge(): void
    {
        $primary = ResourceInfo::create(Attributes::create(['name' => 'primary', 'empty' => '']));
        $secondary = ResourceInfo::create(Attributes::create(['version' => '1.0.0', 'empty' => 'value']));
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
     * @dataProvider environmentResourceProvider
     *
     * @param array<non-empty-string, bool|int|float|string|array> $userAttributes
     * @param array<non-empty-string, bool|int|float|string|array> $expected
     */
    public function test_resource_from_environment(string $envAttributes, array $userAttributes, array $expected): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', $envAttributes);
        $resource = ResourceInfo::create(Attributes::create($userAttributes));
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
        $resource = ResourceInfo::create(Attributes::create());
        $this->assertEquals('unknown_service', $resource->getAttributes()->get('service.name'));
    }

    public function test_resource_with_empty_environment_variable(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', '');
        $this->assertInstanceOf(ResourceInfo::class, ResourceInfo::create(Attributes::create()));
    }

    public function test_resource_with_invalid_environment_variable(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'foo');
        $this->assertInstanceOf(ResourceInfo::class, ResourceInfo::create(Attributes::create()));
    }

    public function test_resource_from_environment_service_name_takes_precedence_over_resource_attribute(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'service.name=bar');
        $this->setEnvironmentVariable('OTEL_SERVICE_NAME', 'foo');
        $resource = ResourceInfo::create(Attributes::create());
        $this->assertEquals('foo', $resource->getAttributes()->get('service.name'));
    }

    public function test_resource_from_environment_resource_attribute_takes_precedence_over_default(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'service.name=foo');
        $resource = ResourceInfo::create(Attributes::create());
        $this->assertEquals('foo', $resource->getAttributes()->get('service.name'));
    }
}
