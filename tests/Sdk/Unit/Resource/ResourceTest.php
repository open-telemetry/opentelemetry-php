<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Resource;

use OpenTelemetry\Sdk\Resource\ResourceConstants;
use OpenTelemetry\Sdk\Resource\ResourceInfo;
use OpenTelemetry\Sdk\Trace\Attribute;
use OpenTelemetry\Sdk\Trace\Attributes;
use PHPUnit\Framework\TestCase;

class ResourceTest extends TestCase
{
    /**
     * @test
     */
    public function testEmptyResource()
    {
        $resource = ResourceInfo::emptyResource();
        $this->assertEmpty($resource->getAttributes());
    }
    /**
     * @test
     */
    public function testGetAttributes()
    {
        $attributes = new Attributes();
        $attributes->setAttribute('name', 'test');
        $resource = ResourceInfo::create($attributes);

        /** @var Attribute $name */
        $name = $resource->getAttributes()->getAttribute('name');

        $sdkname = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_NAME);
        $sdklanguage = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_LANGUAGE);
        $sdkversion = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_VERSION);

        $attributes->setAttribute(ResourceConstants::TELEMETRY_SDK_NAME, 'opentelemetry');
        $attributes->setAttribute(ResourceConstants::TELEMETRY_SDK_LANGUAGE, 'php');
        $attributes->setAttribute(ResourceConstants::TELEMETRY_SDK_VERSION, 'dev');

        $this->assertEquals($attributes, $resource->getAttributes());
        $this->assertEquals('opentelemetry', $sdkname->getValue());
        $this->assertEquals('php', $sdklanguage->getValue());
        $this->assertEquals('dev', $sdkversion->getValue());
        $this->assertEquals($attributes, $resource->getAttributes());
        $this->assertEquals('test', $name->getValue());
    }

    /**
     * @test
     */
    public function testDefaultResource()
    {
        $attributes = new Attributes(
            [
                ResourceConstants::TELEMETRY_SDK_NAME => 'opentelemetry',
                ResourceConstants::TELEMETRY_SDK_LANGUAGE => 'php',
                ResourceConstants::TELEMETRY_SDK_VERSION => 'dev',
            ]
        );
        $resource = ResourceInfo::create(new Attributes());

        $sdkname = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_NAME);
        $sdklanguage = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_LANGUAGE);
        $sdkversion = $resource->getAttributes()->getAttribute(ResourceConstants::TELEMETRY_SDK_VERSION);

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

        $this->assertCount(6, $result->getAttributes());
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
}
