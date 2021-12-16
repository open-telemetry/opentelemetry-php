<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit;

use OpenTelemetry\SDK\GlobalLoggerHolder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class GlobalLoggerHolderTest extends TestCase
{
    public function tearDown(): void
    {
        GlobalLoggerHolder::unset();
    }

    /**
     * @test
     */
    public function setAndGet(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        $this->assertFalse(GlobalLoggerHolder::isSet());
        GlobalLoggerHolder::set($logger);
        $this->assertTrue(GlobalLoggerHolder::isSet());
        $this->assertSame($logger, GlobalLoggerHolder::get());
        GlobalLoggerHolder::unset();
        $this->assertFalse(GlobalLoggerHolder::isSet());
    }

    /**
     * @test
     */
    public function returnsDefaultLoggerWhenNotSet(): void
    {
        $this->assertFalse(GlobalLoggerHolder::isSet());
        $logger = GlobalLoggerHolder::get();
        $this->assertFalse(GlobalLoggerHolder::isSet());
    }

    /**
     * @test
     */
    public function disableCreatesNullLogger(): void
    {
        $this->assertFalse(GlobalLoggerHolder::isSet());
        GlobalLoggerHolder::disable();
        $this->assertTrue(GlobalLoggerHolder::isSet());
        $this->assertInstanceOf(NullLogger::class, GlobalLoggerHolder::get());
    }
}
