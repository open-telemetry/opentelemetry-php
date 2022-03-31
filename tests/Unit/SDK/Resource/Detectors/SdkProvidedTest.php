<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\Detectors;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Resource\Detectors\SdkProvided
 */
class SdkProvidedTest extends TestCase
{
    public function test_sdk_provided_get_resource(): void
    {
        $resouceDetector = new Detectors\SdkProvided();
        $resource = $resouceDetector->getResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertSame('unknown_service', $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }
}
