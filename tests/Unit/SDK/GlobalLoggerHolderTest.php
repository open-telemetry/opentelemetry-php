<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use OpenTelemetry\SDK\GlobalLoggerHolder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @covers OpenTelemetry\SDK\GlobalLoggerHolder
 */
class GlobalLoggerHolderTest extends TestCase
{
    public function tearDown(): void
    {
        GlobalLoggerHolder::unset();
    }

    public function test_set_and_get(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        $this->assertFalse(GlobalLoggerHolder::isSet());
        GlobalLoggerHolder::set($logger);
        $this->assertTrue(GlobalLoggerHolder::isSet());
        $this->assertSame($logger, GlobalLoggerHolder::get());
        GlobalLoggerHolder::unset();
        $this->assertFalse(GlobalLoggerHolder::isSet());
    }

    public function test_returns_default_logger_when_not_set(): void
    {
        $this->assertFalse(GlobalLoggerHolder::isSet());
        $logger = GlobalLoggerHolder::get();
        $this->assertFalse(GlobalLoggerHolder::isSet());
    }

    public function test_disable_creates_null_logger(): void
    {
        $this->assertFalse(GlobalLoggerHolder::isSet());
        GlobalLoggerHolder::disable();
        $this->assertTrue(GlobalLoggerHolder::isSet());
        $this->assertInstanceOf(NullLogger::class, GlobalLoggerHolder::get());
    }
}
