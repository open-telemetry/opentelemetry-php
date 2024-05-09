<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\Detectors;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Resource\Detectors\ProcessRuntime::class)]
class ProcessRuntimeTest extends TestCase
{
    public function test_process_runtime_get_resource(): void
    {
        $resouceDetector = new Detectors\ProcessRuntime();
        $resource = $resouceDetector->getResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_NAME));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_VERSION));
    }
}
