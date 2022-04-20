<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Resource\ResourceInfoFactory
 */
class ResourceInfoFactoryTest extends TestCase
{
    use EnvironmentVariables;

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    public function test_empty_resource(): void
    {
        $resource = ResourceInfoFactory::emptyResource();
        $this->assertEmpty($resource->getAttributes());
    }

    public function test_merge(): void
    {
        $primary = ResourceInfo::create(new Attributes(['name' => 'primary', 'empty' => '']));
        $secondary = ResourceInfo::create(new Attributes(['version' => '1.0.0', 'empty' => 'value']));
        $result = ResourceInfoFactory::merge($primary, $secondary);

        $name = $result->getAttributes()->get('name');
        $version = $result->getAttributes()->get('version');
        $empty = $result->getAttributes()->get('empty');

        $this->assertCount(3, $result->getAttributes());
        $this->assertEquals('primary', $name);
        $this->assertEquals('1.0.0', $version);
        $this->assertEquals('', $empty);
    }

    /**
     * @dataProvider schemaUrlsToMergeProvider
     */
    public function test_merge_schema_url(array $schemaUrlsToMerge, ?string $expectedSchemaUrl): void
    {
        $resourcesToMerge = [];
        foreach ($schemaUrlsToMerge as $schemaUrl) {
            $resourcesToMerge[] = ResourceInfo::create(new Attributes([]), $schemaUrl);
        }
        $result = ResourceInfoFactory::merge(...$resourcesToMerge);

        $this->assertEquals($expectedSchemaUrl, $result->getSchemaUrl());
    }

    public function schemaUrlsToMergeProvider()
    {
        yield 'Should keep old schemaUrl when the updating one is empty' => [['http://url', null], 'http://url'];
        yield 'Should override empty old schemaUrl with non-empty updating one' => [[null, 'http://url'], 'http://url'];
        yield 'Should keep matching schemaUrls' => [['http://url', 'http://url'], 'http://url'];
        yield 'Should resolve an empty schemaUrl when the old and the updating are not equal' => [['http://url-1', 'http://url-2'], null];
        yield 'Should keep empty schemaUrl when not equal schemas have been merged before' => [['http://url-1', 'http://url-2', 'http://url-2'], null];
    }

    public function test_resource_service_name_default(): void
    {
        $resource = ResourceInfoFactory::defaultResource();
        $this->assertEquals('unknown_service', $resource->getAttributes()->get('service.name'));
    }

    /**
     * @group compliance
     */
    public function test_resource_with_empty_environment_variable(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', '');
        $this->assertInstanceOf(ResourceInfo::class, ResourceInfoFactory::defaultResource());
    }

    /**
     * @group compliance
     */
    public function test_resource_with_invalid_environment_variable(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'foo');
        $this->expectException(InvalidArgumentException::class);
        $this->assertInstanceOf(ResourceInfo::class, ResourceInfoFactory::defaultResource());
    }

    /**
     * @group compliance
     */
    public function test_resource_from_environment_service_name_takes_precedence_over_resource_attribute(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'service.name=bar');
        $this->setEnvironmentVariable('OTEL_SERVICE_NAME', 'foo');
        $resource = ResourceInfoFactory::defaultResource();
        $this->assertEquals('foo', $resource->getAttributes()->get('service.name'));
    }

    /**
     * @group compliance
     */
    public function test_resource_from_environment_resource_attribute_takes_precedence_over_default(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'service.name=foo');
        $resource = ResourceInfoFactory::defaultResource();
        $this->assertEquals('foo', $resource->getAttributes()->get('service.name'));
    }
}
