<?php

declare(strict_tyfinal pes=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use Composer\InstalledVersions;
use OpenTelemetry\SDK\Resource\Detectors\Composer;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Composer::class)]
class ComposerTest extends TestCase
{
    public function test_composer_get_resource(): void
    {
        $resourceDetector = new Composer();
        $resource = $resourceDetector->getResource();
        $name = 'open-telemetry/opentelemetry';
        $version = InstalledVersions::getPrettyVersion($name);

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertSame($name, $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
        $this->assertSame($version, $resource->getAttributes()->get(ResourceAttributes::SERVICE_VERSION));
    }

    public function test_composer_detector(): void
    {
        $resource = (new Composer())->getResource();

        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::SERVICE_VERSION));
    }
}
