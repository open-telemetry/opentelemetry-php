<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Resource;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\Attribute;
use OpenTelemetry\SDK\Trace\Attributes;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\TestCase;

class ResourceTest extends TestCase
{
    use EnvironmentVariables;

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    public function testEmptyResource(): void
    {
        $resource = ResourceInfo::emptyResource();
        $this->assertEmpty($resource->getAttributes());
    }

    public function testGetAttributes(): void
    {
        $attributes = new Attributes();
        $attributes->setAttribute('name', 'test');
        $resource = ResourceInfo::create($attributes);

        /** @var Attribute $name */
        $name = $resource->getAttributes()->getAttribute('name');
        /** @var Attribute $sdkname */
        $sdkname = $resource->getAttributes()->getAttribute(ResourceAttributes::TELEMETRY_SDK_NAME);
        /** @var Attribute $sdklanguage */
        $sdklanguage = $resource->getAttributes()->getAttribute(ResourceAttributes::TELEMETRY_SDK_LANGUAGE);
        /** @var Attribute $sdkversion */
        $sdkversion = $resource->getAttributes()->getAttribute(ResourceAttributes::TELEMETRY_SDK_VERSION);

        $attributes->setAttribute(ResourceAttributes::TELEMETRY_SDK_NAME, 'opentelemetry');
        $attributes->setAttribute(ResourceAttributes::TELEMETRY_SDK_LANGUAGE, 'php');
        $attributes->setAttribute(ResourceAttributes::TELEMETRY_SDK_VERSION, 'dev');
        $attributes->setAttribute(ResourceAttributes::SERVICE_NAME, 'unknown_service');

        $this->assertEquals($attributes, $resource->getAttributes());
        $this->assertSame('opentelemetry', $sdkname->getValue());
        $this->assertSame('php', $sdklanguage->getValue());
        $this->assertSame('dev', $sdkversion->getValue());
        $this->assertSame('test', $name->getValue());
    }

    /**
     * @test
     */
    public function testDefaultResource()
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
        /** @var Attribute $sdkname */
        $sdkname = $resource->getAttributes()->getAttribute(ResourceAttributes::TELEMETRY_SDK_NAME);
        /** @var Attribute $sdklanguage */
        $sdklanguage = $resource->getAttributes()->getAttribute(ResourceAttributes::TELEMETRY_SDK_LANGUAGE);
        /** @var Attribute $sdkversion */
        $sdkversion = $resource->getAttributes()->getAttribute(ResourceAttributes::TELEMETRY_SDK_VERSION);

        $this->assertEquals($attributes, $resource->getAttributes());
        $this->assertEquals('opentelemetry', $sdkname->getValue());
        $this->assertEquals('php', $sdklanguage->getValue());
        $this->assertEquals('dev', $sdkversion->getValue());
    }

    /**
     * @test
     */
    public function testMerge()
    {
        $primary = ResourceInfo::create(new Attributes(['name' => 'primary', 'empty' => '']));
        $secondary = ResourceInfo::create(new Attributes(['version' => '1.0.0', 'empty' => 'value']));
        $result = ResourceInfo::merge($primary, $secondary);

        /** @var Attribute $name */
        $name = $result->getAttributes()->getAttribute('name');
        /** @var Attribute $version */
        $version = $result->getAttributes()->getAttribute('version');
        /** @var Attribute $empty */
        $empty = $result->getAttributes()->getAttribute('empty');

        $this->assertCount(7, $result->getAttributes());
        $this->assertEquals('primary', $name->getValue());
        $this->assertEquals('1.0.0', $version->getValue());
        $this->assertEquals('value', $empty->getValue());
    }

    /**
     * @test
     */
    public function testImmutableCreate()
    {
        $attributes = new Attributes();
        $attributes->setAttribute('name', 'test');
        $attributes->setAttribute('version', '1.0.0');

        $resource = ResourceInfo::create($attributes);

        $attributes->setAttribute('version', '2.0.0');

        /** @var Attribute $version */
        $version = $resource->getAttributes()->getAttribute('version');

        $this->assertEquals('1.0.0', $version->getValue());
    }

    /**
     * @test
     * @dataProvider environmentResourceProvider
     */
    public function resource_fromEnvironment(string $envAttributes, array $userAttributes, array $expected)
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
    public function resource_serviceNameDefault()
    {
        $resource = ResourceInfo::create(new Attributes([]));
        $this->assertEquals('unknown_service', $resource->getAttributes()->get('service.name'));
    }

    /**
     * @test
     */
    public function resource_withEmptyEnvironmentVariable()
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', '');
        $this->assertInstanceOf(ResourceInfo::class, ResourceInfo::create(new Attributes([])));
    }

    /**
     * @test
     */
    public function resource_withInvalidEnvironmentVariable()
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'foo');
        $this->assertInstanceOf(ResourceInfo::class, ResourceInfo::create(new Attributes([])));
    }

    /**
     * @test
     */
    public function resource_fromEnvironment_serviceNameTakesPrecedenceOverResourceAttribute()
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'service.name=bar');
        $this->setEnvironmentVariable('OTEL_SERVICE_NAME', 'foo');
        $resource = ResourceInfo::create(new Attributes([]));
        $this->assertEquals('foo', $resource->getAttributes()->get('service.name'));
    }

    /**
     * @test
     */
    public function resource_fromEnvironment_resourceAttributeTakesPrecedenceOverDefault()
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'service.name=foo');
        $resource = ResourceInfo::create(new Attributes([]));
        $this->assertEquals('foo', $resource->getAttributes()->get('service.name'));
    }
}
