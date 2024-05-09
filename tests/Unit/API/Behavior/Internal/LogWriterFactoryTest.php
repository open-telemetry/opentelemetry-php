<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Behavior\Internal;

use OpenTelemetry\API\Behavior\Internal\LogWriter\ErrorLogWriter;
use OpenTelemetry\API\Behavior\Internal\LogWriter\NoopLogWriter;
use OpenTelemetry\API\Behavior\Internal\LogWriter\Psr3LogWriter;
use OpenTelemetry\API\Behavior\Internal\LogWriter\StreamLogWriter;
use OpenTelemetry\API\Behavior\Internal\LogWriterFactory;
use OpenTelemetry\API\LoggerHolder;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \OpenTelemetry\API\Behavior\Internal\LogWriterFactory
 */
class LogWriterFactoryTest extends TestCase
{
    use TestState;

    public function setUp(): void
    {
        LoggerHolder::unset();
    }

    public function tearDown(): void
    {
        self::restoreEnvironmentVariables();
        LoggerHolder::unset();
    }

    /**
     * @dataProvider logDestinationProvider
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function test_log_destination_from_env(string $value, string $expected): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_LOG_DESTINATION', $value);
        $this->assertInstanceOf($expected, (new LogWriterFactory())->create());
    }

    public static function logDestinationProvider(): array
    {
        return [
            ['error_log', ErrorLogWriter::class],
            ['stdout', StreamLogWriter::class],
            ['stderr', StreamLogWriter::class],
            ['none', NoopLogWriter::class],
            ['', ErrorLogWriter::class],
        ];
    }

    public function test_psr3_log_destination(): void
    {
        LoggerHolder::set($this->createMock(LoggerInterface::class));
        $this->assertInstanceOf(Psr3LogWriter::class, (new LogWriterFactory())->create());
    }
}
