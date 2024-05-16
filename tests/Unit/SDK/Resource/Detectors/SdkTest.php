<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use Composer\InstalledVersions;
use OpenTelemetry\SDK\Resource\Detectors\Sdk;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Sdk::class)]
class SdkTest extends TestCase
{
    public function test_sdk_get_resource(): void
    {
        $resourceDetector = new Sdk();
        $resource = $resourceDetector->getResource();
        $version = InstalledVersions::getPrettyVersion('open-telemetry/opentelemetry');

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertSame('opentelemetry', $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME));
        $this->assertSame('php', $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE));
        $this->assertSame($version, $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_VERSION));
    }
}
