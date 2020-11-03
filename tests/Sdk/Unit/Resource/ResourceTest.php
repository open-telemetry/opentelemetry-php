<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Sdk\Unit\Resource;

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

    public function testGetAttributes()
    {
        $attributes = new Attributes();
        $attributes->setAttribute('name', 'test');
        $resource = ResourceInfo::create($attributes);

        /** @var Attribute $name */
        $name = $resource->getAttributes()->getAttribute('name');

        $this->assertEquals($attributes, $resource->getAttributes());
        $this->assertEquals('test', $name->getValue());
    }

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

        $this->assertCount(3, $result->getAttributes());
        $this->assertEquals('primary', $name->getValue());
        $this->assertEquals('1.0.0', $version->getValue());
        $this->assertEquals('value', $empty->getValue());
    }

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
