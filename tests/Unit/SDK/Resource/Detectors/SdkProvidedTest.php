<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\Detectors\SdkProvided;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SdkProvided::class)]
final class SdkProvidedTest extends TestCase
{
    public function test_get_resource_returns_empty_resource(): void
    {
        $detector = new SdkProvided();
        $resource = $detector->getResource();

        $this->assertInstanceOf(ResourceInfo::class, $resource);
        $this->assertCount(0, $resource->getAttributes());
    }
}
