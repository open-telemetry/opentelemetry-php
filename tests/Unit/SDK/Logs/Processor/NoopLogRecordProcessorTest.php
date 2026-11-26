<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs\Processor;

use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Logs\Processor\NoopLogRecordProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopLogRecordProcessor::class)]
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

    public function test_is_not_enabled(): void
    {
        $this->assertFalse(NoopLogRecordProcessor::getInstance()->isEnabled(
            Context::getCurrent(),
            $this->createStub(InstrumentationScopeInterface::class),
            null,
            null,
        ));
    }
}
