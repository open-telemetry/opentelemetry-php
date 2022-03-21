<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\Detectors;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Resource\Detectors\Composite
 */
class CompositeTest extends TestCase
{
    public function test_composite_with_empty_resource_detectors(): void
    {
        $resouceDetector = new Detectors\Composite([]);
        $resource = $resouceDetector->getResource();

        $this->assertNull($resource->getSchemaUrl());
        $this->assertEmpty($resource->getAttributes());
    }

    /**
     * @dataProvider resourceDetectorProvider
     */
    public function test_composite_get_resource(iterable $resourceDetectors, array $expectedAttributes, ?string $expectedSchemaURL): void
    {
        $resource = (new Detectors\Composite($resourceDetectors))->getResource();
        foreach ($expectedAttributes as $name => $value) {
            $this->assertSame($value, $resource->getAttributes()->get($name));
        }
        $this->assertSame($expectedSchemaURL, $resource->getSchemaUrl());
    }

    public function resourceDetectorProvider()
    {
        return [
            'Sdks' => [
                [
                    new Detectors\Sdk(),
                    new Detectors\SdkProvided(),
                ],
                [
                    ResourceAttributes::TELEMETRY_SDK_NAME => 'opentelemetry',
                    ResourceAttributes::TELEMETRY_SDK_LANGUAGE => 'php',
                    ResourceAttributes::SERVICE_NAME => 'unknown_service',
                ],
                ResourceAttributes::SCHEMA_URL,
            ],
            'Constant' => [
                [
                    new Detectors\Constant(ResourceInfo::create(new Attributes(['foo' => 'constant-foo', 'bar' => 'constant-bar']))),
                ],
                [
                    'foo' => 'constant-foo',
                    'bar' => 'constant-bar',
                ],
                null,
            ],
        ];
    }
}
