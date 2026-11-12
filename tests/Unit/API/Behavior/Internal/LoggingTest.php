<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Behavior\Internal;

use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

#[CoversClass(Logging::class)]
class LoggingTest extends TestCase
{
    use TestState;

    #[\Override]
    public function setUp(): void
    {
        Logging::reset();
    }

    public function test_unknown_level_name_falls_back_to_info(): void
    {
        $this->assertSame(Logging::level(LogLevel::INFO), Logging::level('not-a-level'));
    }

    public function test_debug_is_lower_than_info(): void
    {
        $this->assertLessThan(Logging::level(LogLevel::INFO), Logging::level(LogLevel::DEBUG));
    }

    public function test_unset_otel_log_level_uses_info_without_warning(): void
    {
        $this->setEnvironmentVariable('OTEL_LOG_LEVEL', null);
        $writer = $this->createMock(LogWriterInterface::class);
        $writer->expects($this->never())->method('write');
        Logging::setLogWriter($writer);

        $this->assertSame(Logging::level(LogLevel::INFO), Logging::logLevel());
    }

    #[DataProvider('knownLevelProvider')]
    public function test_known_otel_log_level_is_used_without_warning(string $level): void
    {
        $this->setEnvironmentVariable('OTEL_LOG_LEVEL', $level);
        $writer = $this->createMock(LogWriterInterface::class);
        $writer->expects($this->never())->method('write');
        Logging::setLogWriter($writer);

        $this->assertSame(Logging::level($level), Logging::logLevel());
    }

    public static function knownLevelProvider(): array
    {
        return [
            'debug' => [LogLevel::DEBUG],
            'warning' => [LogLevel::WARNING],
            'none' => ['none'],
        ];
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/configuration/sdk-environment-variables.md#enum
     */
    public function test_unknown_otel_log_level_warns_once_and_uses_info(): void
    {
        $this->setEnvironmentVariable('OTEL_LOG_LEVEL', 'eror');
        $writer = $this->createMock(LogWriterInterface::class);
        $writer->expects($this->once())->method('write')->with(
            $this->equalTo(LogLevel::WARNING),
            $this->logicalAnd(
                $this->stringContains('OTEL_LOG_LEVEL'),
                $this->stringContains('"eror"'),
            ),
            $this->anything(),
        );
        Logging::setLogWriter($writer);

        $this->assertSame(Logging::level(LogLevel::INFO), Logging::logLevel());
        $this->assertSame(Logging::level(LogLevel::INFO), Logging::logLevel());
    }

    public function test_non_string_otel_log_level_warns_and_uses_info(): void
    {
        // records the original value, so that TestState restores $_SERVER afterwards
        $this->setEnvironmentVariable('OTEL_LOG_LEVEL', null);
        $_SERVER['OTEL_LOG_LEVEL'] = ['debug'];
        $writer = $this->createMock(LogWriterInterface::class);
        $writer->expects($this->once())->method('write')->with(
            $this->equalTo(LogLevel::WARNING),
            $this->stringContains('array'),
            $this->anything(),
        );
        Logging::setLogWriter($writer);

        $this->assertSame(Logging::level(LogLevel::INFO), Logging::logLevel());
    }

    public function test_failing_writer_does_not_break_resolving_unknown_otel_log_level(): void
    {
        $this->setEnvironmentVariable('OTEL_LOG_LEVEL', 'eror');
        $writer = $this->createMock(LogWriterInterface::class);
        $writer->method('write')->willThrowException(new \RuntimeException('boom'));
        Logging::setLogWriter($writer);

        $this->assertSame(Logging::level(LogLevel::INFO), Logging::logLevel());
    }
}
