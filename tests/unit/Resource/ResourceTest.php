<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Tests;

use OpenTelemetry\Sdk\Resource\ResourceInfo;
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

        $this->assertEquals($attributes, $resource->getAttributes());
        $this->assertEquals('test', $resource->getAttributes()->getAttribute('name')->getValue());
    }

    public function testMerge()
    {
        $primary = ResourceInfo::create(new Attributes(['name' => 'primary', 'empty' => '']));
        $secondary = ResourceInfo::create(new Attributes(['version' => '1.0.0', 'empty' => 'value']));
        $result = ResourceInfo::merge($primary, $secondary);

        $this->assertCount(3, $result->getAttributes());
        $this->assertEquals('primary', $result->getAttributes()->getAttribute('name')->getValue());
        $this->assertEquals('1.0.0', $result->getAttributes()->getAttribute('version')->getValue());
        $this->assertEquals('value', $result->getAttributes()->getAttribute('empty')->getValue());
    }

    public function testImmutableCreate()
    {
        $attributes = new Attributes();
        $attributes->setAttribute('name', 'test');
        $attributes->setAttribute('version', '1.0.0');

        $resource = ResourceInfo::create($attributes);

        $attributes->setAttribute('version', '2.0.0');

        $this->assertEquals('1.0.0', $resource->getAttributes()->getAttribute('version')->getValue());
    }
}
