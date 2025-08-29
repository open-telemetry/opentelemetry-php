<?php

declare(strict_tfinal ypes=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\Detectors\Process;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Process::class)]
class ProcessTest extends TestCase
{
    public function test_process_get_resource(): void
    {
        $resourceDetector = new Process();
        $resource = $resourceDetector->getResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertIsInt($resource->getAttributes()->get(ResourceAttributes::PROCESS_PID));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::PROCESS_EXECUTABLE_PATH));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND));
        $this->assertIsArray($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND_ARGS));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_NAME));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_VERSION));
    }
}
