<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use Composer\InstalledVersions;
use OpenTelemetry\SDK\Resource\Detectors;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Resource\Detectors\Sdk
 */
class SdkTest extends TestCase
{
    public function test_sdk_get_resource(): void
    {
        $resouceDetector = new Detectors\Sdk();
        $resource = $resouceDetector->getResource();
        $version = InstalledVersions::getVersion('open-telemetry/opentelemetry');

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertSame('opentelemetry', $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME));
        $this->assertSame('php', $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE));
        $this->assertSame($version, $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_VERSION));
    }
}
