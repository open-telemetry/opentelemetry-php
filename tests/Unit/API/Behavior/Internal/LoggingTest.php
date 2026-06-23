<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Behavior\Internal;

use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\API\Behavior\Internal\LogWriter\NoopLogWriter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Logging::class)]
class LoggingTest extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        Logging::reset();
    }

    #[\Override]
    protected function tearDown(): void
    {
        Logging::reset();
    }

    public function test_set_log_writer(): void
    {
        $writer = $this->createMock(LogWriterInterface::class);
        Logging::setLogWriter($writer);
        $this->assertSame($writer, Logging::logWriter());
    }

    public function test_log_writer_creates_default(): void
    {
        $writer = Logging::logWriter();
        $this->assertInstanceOf(LogWriterInterface::class, $writer);
    }

    public function test_disable_sets_noop_writer(): void
    {
        Logging::disable();
        $this->assertInstanceOf(NoopLogWriter::class, Logging::logWriter());
    }

    public function test_reset_clears_state(): void
    {
        $writer = $this->createMock(LogWriterInterface::class);
        Logging::setLogWriter($writer);
        Logging::reset();
        $this->assertNotSame($writer, Logging::logWriter());
    }

    public function test_level_returns_index(): void
    {
        // 'debug' is index 0 but level() returns ?: 1, so debug maps to info level
        $this->assertSame(1, Logging::level('debug'));
        $this->assertSame(1, Logging::level('info'));
        $this->assertSame(3, Logging::level('warning'));
        $this->assertSame(4, Logging::level('error'));
    }

    public function test_level_returns_info_for_unknown(): void
    {
        $this->assertSame(1, Logging::level('unknown'));
    }

    public function test_log_level_returns_default(): void
    {
        $level = Logging::logLevel();
        $this->assertIsInt($level);
    }
}
