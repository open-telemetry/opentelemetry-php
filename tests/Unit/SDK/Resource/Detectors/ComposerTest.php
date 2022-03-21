<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use Composer\InstalledVersions;
use OpenTelemetry\SDK\Resource\Detectors;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Resource\Detectors\Composer
 */
class ComposerTest extends TestCase
{
    public function test_composer_get_resource(): void
    {
        $resouceDetector = new Detectors\Composer();
        $resource = $resouceDetector->getResource();
        $name = 'open-telemetry/opentelemetry';
        $version = InstalledVersions::getPrettyVersion($name);

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertSame($name, $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
        $this->assertSame($version, $resource->getAttributes()->get(ResourceAttributes::SERVICE_VERSION));
    }
}
