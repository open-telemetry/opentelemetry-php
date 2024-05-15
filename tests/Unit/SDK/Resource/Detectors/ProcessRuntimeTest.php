<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\Detectors\ProcessRuntime;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProcessRuntime::class)]
class ProcessRuntimeTest extends TestCase
{
    public function test_process_runtime_get_resource(): void
    {
        $resourceDetector = new ProcessRuntime();
        $resource = $resourceDetector->getResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_NAME));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_VERSION));
    }
}
