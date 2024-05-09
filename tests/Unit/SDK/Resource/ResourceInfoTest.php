<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource;

use Composer\InstalledVersions;
use Generator;
use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\Detectors;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SemConv\ResourceAttributes;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Resource\ResourceInfo::class)]
class ResourceInfoTest extends TestCase
{
    use TestState;

    public function setUp(): void
    {
        Logging::disable();
    }

    public function test_get_attributes(): void
    {
        $attributes = Attributes::create(['name' => 'test']);

        $resource = (new Detectors\Composite([
            new Detectors\Constant(ResourceInfo::create($attributes)),
            new Detectors\Sdk(),
            new Detectors\SdkProvided(),
        ]))->getResource();

        $version = InstalledVersions::getPrettyVersion('open-telemetry/opentelemetry');

        $name = $resource->getAttributes()->get('name');
        $sdkname = $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME);
        $sdklanguage = $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE);
        $sdkversion = $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_VERSION);

        $this->assertSame('opentelemetry', $sdkname);
        $this->assertSame('php', $sdklanguage);
        $this->assertSame($version, $sdkversion);
        $this->assertSame('test', $name);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('sameResourcesProvider')]
    public function test_serialize_returns_same_output_for_objects_representing_the_same_resource(ResourceInfo $resource1, ResourceInfo $resource2): void
    {
        $this->assertSame($resource1->serialize(), $resource2->serialize());
    }

    public static function sameResourcesProvider(): iterable
    {
        yield 'Attribute keys sorted in ascending order vs Attribute keys sorted in descending order' => [
            ResourceInfo::create(Attributes::create([
                'a' => 'someValue',
                'b' => 'someValue',
                'c' => 'someValue',
            ])),
            ResourceInfo::create(Attributes::create([
                'c' => 'someValue',
                'b' => 'someValue',
                'a' => 'someValue',
            ])),
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('differentResourcesProvider')]
    public function test_serialize_returns_different_output_for_objects_representing_different_resources(ResourceInfo $resource1, ResourceInfo $resource2): void
    {
        $this->assertNotSame($resource1->serialize(), $resource2->serialize());
    }

    public static function differentResourcesProvider(): iterable
    {
        yield 'Null schema url vs Some schema url' => [
            ResourceInfo::create(Attributes::create([]), null),
            ResourceInfo::create(Attributes::create([]), 'someSchemaUrl'),
        ];
    }

    public function test_serialize_incorporates_all_properties(): void
    {
        $resource = ResourceInfoFactory::emptyResource();
        $properties = (new \ReflectionClass($resource))->getProperties();

        $serializedResource = $resource->serialize();

        foreach ($properties as $property) {
            $this->assertStringContainsString($property->getName(), $serializedResource);
        }
    }

    public function test_merge(): void
    {
        $primary = ResourceInfo::create(Attributes::create(['name' => 'primary', 'empty' => '']));
        $secondary = ResourceInfo::create(Attributes::create(['version' => '1.0.0', 'empty' => 'value']));
        $result = $primary->merge($secondary);

        $name = $result->getAttributes()->get('name');
        $version = $result->getAttributes()->get('version');
        $empty = $result->getAttributes()->get('empty');

        $this->assertCount(3, $result->getAttributes());
        $this->assertEquals('primary', $name);
        $this->assertEquals('1.0.0', $version);
        $this->assertEquals('value', $empty);
    }

    #[\PHPUnit\Framework\Attributes\Group('compliance
"If a key exists on both the old and updating resource, the value of the updating resource MUST be picked (even if the updated value is empty)"')]
    public function test_merge_uses_value_of_updating_resource(): void
    {
        $old = ResourceInfo::create(Attributes::create(['name' => 'original', 'foo' => 'bar']));
        $updating = ResourceInfo::create(Attributes::create(['name' => 'updated', 'foo' => '']));
        $merged = $old->merge($updating);

        $this->assertSame('updated', $merged->getAttributes()->get('name'));
        $this->assertSame('', $merged->getAttributes()->get('foo'));
    }

    public function test_merge_with_numeric_attribute_keys(): void
    {
        $old = ResourceInfo::create(Attributes::create([1 => 'one', '2' => 'two']));
        $updating = ResourceInfo::create(Attributes::create(['1' => 'one.upd', 2 => 'two.upd']));
        $merged = $old->merge($updating);

        $this->assertSame('one.upd', $merged->getAttributes()->get('1'));
        $this->assertSame('two.upd', $merged->getAttributes()->get('2'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('schemaUrlsToMergeProvider')]
    public function test_merge_schema_url(array $schemaUrlsToMerge, ?string $expectedSchemaUrl): void
    {
        $old = ResourceInfo::create(Attributes::create([]), $schemaUrlsToMerge[0]);
        $updating = ResourceInfo::create(Attributes::create([]), $schemaUrlsToMerge[1]);
        $result = $old->merge($updating);

        $this->assertSame($expectedSchemaUrl, $result->getSchemaUrl());
    }

    public static function schemaUrlsToMergeProvider(): Generator
    {
        yield 'Should keep old schemaUrl when the updating one is empty' => [['http://url', null], 'http://url'];
        yield 'Should override empty old schemaUrl with non-empty updating one' => [[null, 'http://url'], 'http://url'];
        yield 'Should keep matching schemaUrls' => [['http://url', 'http://url'], 'http://url'];
        yield 'Should resolve an empty schemaUrl when the old and the updating are not equal' => [['http://url-1', 'http://url-2'], null];
    }
}
