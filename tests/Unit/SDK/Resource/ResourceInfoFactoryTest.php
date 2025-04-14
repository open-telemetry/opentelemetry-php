<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource;

use Generator;
use InvalidArgumentException;
use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResourceInfoFactory::class)]
class ResourceInfoFactoryTest extends TestCase
{
    use TestState;

    /** @var LogWriterInterface&MockObject $logWriter */
    private LogWriterInterface $logWriter;
    private const UNDEFINED = '__undefined';

    public function setUp(): void
    {
        $this->logWriter = $this->createMock(LogWriterInterface::class);
        Logging::setLogWriter($this->logWriter);
    }

    public function test_empty_resource(): void
    {
        $resource = ResourceInfoFactory::emptyResource();
        $this->assertEmpty($resource->getAttributes());
    }

    public function test_merge(): void
    {
        $primary = ResourceInfo::create(Attributes::create(['name' => 'primary', 'empty' => 'value']));
        $secondary = ResourceInfo::create(Attributes::create(['version' => '1.0.0', 'empty' => '']));
        $result = $primary->merge($secondary);

        $name = $result->getAttributes()->get('name');
        $version = $result->getAttributes()->get('version');
        $empty = $result->getAttributes()->get('empty');

        $this->assertCount(3, $result->getAttributes());
        $this->assertEquals('primary', $name);
        $this->assertEquals('1.0.0', $version);
        $this->assertEquals('', $empty);
    }

    #[DataProvider('schemaUrlsToMergeProvider')]
    public function test_merge_schema_url(array $schemaUrlsToMerge, ?string $expectedSchemaUrl): void
    {
        $resource = ResourceInfoFactory::emptyResource();
        foreach ($schemaUrlsToMerge as $schemaUrl) {
            $resource = $resource->merge(ResourceInfo::create(Attributes::create([]), $schemaUrl));
        }

        if ($expectedSchemaUrl === self::UNDEFINED) {
            $this->assertTrue(true, 'dummy assertion');
        } else {
            $this->assertEquals($expectedSchemaUrl, $resource->getSchemaUrl());
        }
    }

    public static function schemaUrlsToMergeProvider(): Generator
    {
        yield 'Should keep old schemaUrl when the updating one is empty' => [['http://url', null], 'http://url'];
        yield 'Should override empty old schemaUrl with non-empty updating one' => [[null, 'http://url'], 'http://url'];
        yield 'Should keep matching schemaUrls' => [['http://url', 'http://url'], 'http://url'];
        yield 'Should resolve an empty schemaUrl when the old and the updating are not equal' => [['http://url-1', 'http://url-2'], null];
        yield 'Schema url is undefined and implementation-specific after merging error' => [['http://url-1', 'http://url-2', 'http://url-2'], self::UNDEFINED];
    }

    #[Group('trace-compliance')]
    public function test_resource_service_name_default(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_DETECTORS', 'all');
        $resource = ResourceInfoFactory::defaultResource();
        $this->assertEquals('open-telemetry/opentelemetry', $resource->getAttributes()->get('service.name'));
    }

    #[Group('compliance')]
    public function test_resource_with_empty_environment_variable(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', '');
        $this->assertInstanceOf(ResourceInfo::class, ResourceInfoFactory::defaultResource());
    }

    #[Group('compliance')]
    public function test_resource_with_invalid_environment_variable(): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'foo');
        $this->expectException(InvalidArgumentException::class);
        $this->assertInstanceOf(ResourceInfo::class, ResourceInfoFactory::defaultResource());
    }

    #[Group('compliance')]
    public function test_resource_from_environment_service_name_takes_precedence_over_resource_attribute(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_DETECTORS', 'all');
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'service.name=bar');
        $this->setEnvironmentVariable('OTEL_SERVICE_NAME', 'foo');
        $resource = ResourceInfoFactory::defaultResource();
        $this->assertEquals('foo', $resource->getAttributes()->get('service.name'));
    }

    #[Group('compliance')]
    public function test_resource_from_environment_resource_attribute_takes_precedence_over_default(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_DETECTORS', 'all');
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', 'service.name=foo');
        $resource = ResourceInfoFactory::defaultResource();
        $this->assertEquals('foo', $resource->getAttributes()->get('service.name'));
    }

    public function test_all_resources_uses_extra_resource_from_spi(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_DETECTORS', 'all');

        $resource = ResourceInfoFactory::defaultResource();
        $this->assertTrue($resource->getAttributes()->has('test-resource'));
        $this->assertSame('test-value', $resource->getAttributes()->get('test-resource'));
    }

    public function test_composite_default_with_extra_resource_from_spi(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_DETECTORS', 'test,sdk');
        $resource = ResourceInfoFactory::defaultResource();

        $this->assertTrue($resource->getAttributes()->has('test-resource'));
        $this->assertTrue($resource->getAttributes()->has('telemetry.sdk.language'));
    }

    public function test_default_with_all_sdk_detectors(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_DETECTORS', 'service,env,host,os,process,process_runtime,sdk,sdk_provided,composer');
        $resource = ResourceInfoFactory::defaultResource();
        $keys = array_keys($resource->getAttributes()->toArray());
        foreach (['service.name', 'telemetry.sdk.name', 'process.runtime.name', 'process.pid', 'host.arch'] as $key) {
            $this->assertContains($key, $keys);
        }
    }

    /**
     * From SDK 2.x, the default detectors are reduced to: sdk, sdk_provided, env
     */
    #[DataProvider('defaultProvider')]
    public function test_default_detectors(?string $value): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_DETECTORS', $value);
        $resource = ResourceInfoFactory::defaultResource();
        $keys = array_keys($resource->getAttributes()->toArray());
        $expected = [
            'telemetry.sdk.name',
            'telemetry.sdk.language',
            'telemetry.sdk.version',
            'telemetry.distro.name',
            'telemetry.distro.version',
            'service.name',
        ];

        $this->assertEquals($expected, $keys);
    }

    public static function defaultProvider(): array
    {
        return [
            ['default'],
            [null],
        ];
    }

    public function test_default_with_none_detectors(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_DETECTORS', 'none');
        $resource = ResourceInfoFactory::defaultResource();
        $keys = array_keys($resource->getAttributes()->toArray());
        $this->assertEmpty($keys);
    }

    public function test_logs_warning_for_unknown_detector(): void
    {
        $this->logWriter->expects($this->once())->method('write')->with($this->equalTo('warning'));
        $this->setEnvironmentVariable('OTEL_PHP_DETECTORS', 'does-not-exist');

        ResourceInfoFactory::defaultResource();
    }
}
