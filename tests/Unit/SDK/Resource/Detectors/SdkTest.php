<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use Composer\InstalledVersions;
use OpenTelemetry\SDK\Resource\Detectors\Sdk;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SemConv\ResourceAttributes;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Sdk::class)]
class SdkTest extends TestCase
{
    use TestState;

    private ResourceDetectorInterface $detector;

    #[\Override]
    public function setUp(): void
    {
        $this->detector = new Sdk();
    }

    public function test_sdk_get_resource(): void
    {
        $resource = $this->detector->getResource();
        $version = InstalledVersions::getPrettyVersion('open-telemetry/opentelemetry');

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertSame('opentelemetry', $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_NAME));
        $this->assertSame('php', $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_LANGUAGE));
        $this->assertSame($version, $resource->getAttributes()->get(ResourceAttributes::TELEMETRY_SDK_VERSION));
    }
}
