<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs\Processor;

use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\NoopLogsProcessor;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Logs\Processor\NoopLogsProcessor
 */
class NoopLogsProcessorTest extends TestCase
{
    public function test_get_instance(): void
    {
        $this->assertInstanceOf(LogRecordProcessorInterface::class, NoopLogsProcessor::getInstance());
    }

    public function test_shutdown(): void
    {
        $this->assertTrue(NoopLogsProcessor::getInstance()->shutdown());
    }

    public function test_force_flush(): void
    {
        $this->assertTrue(NoopLogsProcessor::getInstance()->forceFlush());
    }
}
