<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Composer\InstalledVersions;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\Detectors;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Resource\ResourceInfo
 */
class ResourceInfoTest extends TestCase
{
    use EnvironmentVariables;

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
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

    /**
     * @dataProvider sameResourcesProvider
     */
    public function test_serialize_returns_same_output_for_objects_representing_the_same_resource(ResourceInfo $resource1, ResourceInfo $resource2): void
    {
        $this->assertSame($resource1->serialize(), $resource2->serialize());
    }

    public function sameResourcesProvider(): iterable
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

    /**
     * @dataProvider differentResourcesProvider
     */
    public function test_serialize_returns_different_output_for_objects_representing_different_resources(ResourceInfo $resource1, ResourceInfo $resource2): void
    {
        $this->assertNotSame($resource1->serialize(), $resource2->serialize());
    }

    public function differentResourcesProvider(): iterable
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
}
