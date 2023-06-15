<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API;

use OpenTelemetry\API\LoggerHolder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @covers \OpenTelemetry\API\LoggerHolder
 */
class LoggerHolderTest extends TestCase
{
    public function tearDown(): void
    {
        LoggerHolder::unset();
    }

    public function test_constructor(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        new LoggerHolder($logger);

        $this->assertSame($logger, LoggerHolder::get());
    }

    public function test_set_and_get(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        $this->assertFalse(LoggerHolder::isSet());
        LoggerHolder::set($logger);
        $this->assertTrue(LoggerHolder::isSet());
        $this->assertSame($logger, LoggerHolder::get());
        LoggerHolder::unset();
        $this->assertFalse(LoggerHolder::isSet());
    }

    public function test_returns_default_logger_when_not_set(): void
    {
        $this->assertFalse(LoggerHolder::isSet());
        $logger = LoggerHolder::get();
        $this->assertFalse(LoggerHolder::isSet());
    }

    public function test_disable_creates_null_logger(): void
    {
        $this->assertFalse(LoggerHolder::isSet());
        LoggerHolder::disable();
        $this->assertTrue(LoggerHolder::isSet());
        $this->assertInstanceOf(NullLogger::class, LoggerHolder::get());
    }
}
