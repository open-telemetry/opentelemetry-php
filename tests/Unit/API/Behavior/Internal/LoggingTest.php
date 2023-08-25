<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Behavior\Internal;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\ErrorLogWriter;
use OpenTelemetry\API\Behavior\Internal\LogWriter\NoopLogWriter;
use OpenTelemetry\API\Behavior\Internal\LogWriter\Psr3LogWriter;
use OpenTelemetry\API\Behavior\Internal\LogWriter\StreamLogWriter;
use OpenTelemetry\API\LoggerHolder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \OpenTelemetry\API\Behavior\Internal\Logging
 */
class LoggingTest extends TestCase
{
    use EnvironmentVariables;

    public function tearDown(): void
    {
        self::restoreEnvironmentVariables();
        Logging::reset();
        LoggerHolder::unset();
    }

    /**
     * @dataProvider logDestinationProvider
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function test_set_log_destination_from_env(string $value, string $expected): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_LOG_DESTINATION', $value);
        $this->assertInstanceOf($expected, Logging::logWriter());
    }

    public static function logDestinationProvider(): array
    {
        return [
            ['error_log', ErrorLogWriter::class],
            ['stdout', StreamLogWriter::class],
            ['stderr', StreamLogWriter::class],
            ['none', NoopLogWriter::class],
            ['default', ErrorLogWriter::class],
            ['', ErrorLogWriter::class],
        ];
    }

    public function test_psr3_log_destination(): void
    {
        LoggerHolder::set($this->createMock(LoggerInterface::class));
        $this->assertInstanceOf(Psr3LogWriter::class, Logging::logWriter());
    }
}
