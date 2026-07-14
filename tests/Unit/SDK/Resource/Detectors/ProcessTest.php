<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\Detectors\Process;
use OpenTelemetry\SemConv\Incubating\Attributes\ProcessIncubatingAttributes;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Process::class)]
class ProcessTest extends TestCase
{
    #[BackupGlobals(true)]
    public function test_process_get_resource(): void
    {
        $_SERVER['argv'] = ['artisan', '--password=secret'];

        $resourceDetector = new Process();
        $resource = $resourceDetector->getResource();

        $this->assertStringMatchesFormat('https://opentelemetry.io/schemas/%d.%d.%d', $resource->getSchemaUrl() ?? '');
        $this->assertIsInt($resource->getAttributes()->get(ResourceAttributes::PROCESS_PID));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::PROCESS_EXECUTABLE_PATH));
        $this->assertSame('artisan', $resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND));
        $this->assertSame(2, $resource->getAttributes()->get(ProcessIncubatingAttributes::PROCESS_ARGS_COUNT));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND_ARGS));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::PROCESS_COMMAND_LINE));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_NAME));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::PROCESS_RUNTIME_VERSION));
    }
}
