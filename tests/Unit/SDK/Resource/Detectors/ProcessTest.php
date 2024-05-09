<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\Detectors;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Resource\Detectors\Process::class)]
class ProcessTest extends TestCase
{
    public function test_process_get_resource(): void
    {
        $resouceDetector = new Detectors\Process();
        $resource = $resouceDetector->getResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertIsInt($resource->getAttributes()->get(ResourceAttributes::PROCESS_PID));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::PROCESS_EXECUTABLE_PATH));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND));
        $this->assertIsArray($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND_ARGS));
    }
}
