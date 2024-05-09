<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs\Processor;

use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\NoopLogRecordProcessor;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Logs\Processor\NoopLogRecordProcessor::class)]
class NoopLogRecordProcessorTest extends TestCase
{
    public function test_get_instance(): void
    {
        $this->assertInstanceOf(LogRecordProcessorInterface::class, NoopLogRecordProcessor::getInstance());
    }

    public function test_shutdown(): void
    {
        $this->assertTrue(NoopLogRecordProcessor::getInstance()->shutdown());
    }

    public function test_force_flush(): void
    {
        $this->assertTrue(NoopLogRecordProcessor::getInstance()->forceFlush());
    }
}
