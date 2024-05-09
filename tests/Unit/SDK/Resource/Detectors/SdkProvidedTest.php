<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\Detectors;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Resource\Detectors\SdkProvided::class)]
class SdkProvidedTest extends TestCase
{
    public function test_sdk_provided_get_resource(): void
    {
        $resourceDetector = new Detectors\SdkProvided();
        $resource = $resourceDetector->getResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertSame('unknown_service:php', $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
    }
}
