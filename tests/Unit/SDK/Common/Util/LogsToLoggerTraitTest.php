<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Util;

use OpenTelemetry\SDK\Common\Util\LogsToLoggerTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RuntimeException;

#[CoversClass(LogsToLoggerTrait::class)]
final class LogsToLoggerTraitTest extends TestCase
{
    #[DataProvider('logMethodProvider')]
    public function test_logs_to_logger_at_level(string $method, string $level): void
    {
        $instance = $this->createInstance($logger = $this->createMock(LoggerInterface::class));
        $logger->expects($this->once())->method('log')->with(
            $level,
            'some message',
            ['foo' => 'bar', 'source' => $instance::class],
        );

        $instance->{$method}('some message', ['foo' => 'bar']);
    }

    public static function logMethodProvider(): array
    {
        return [
            ['logDebug', LogLevel::DEBUG],
            ['logInfo', LogLevel::INFO],
            ['logNotice', LogLevel::NOTICE],
            ['logWarning', LogLevel::WARNING],
            ['logError', LogLevel::ERROR],
        ];
    }

    public function test_source_overrides_context_source(): void
    {
        $instance = $this->createInstance($logger = $this->createMock(LoggerInterface::class));
        $logger->expects($this->once())->method('log')->with(
            LogLevel::WARNING,
            'some message',
            ['source' => $instance::class],
        );

        $instance->logWarning('some message', ['source' => 'other']);
    }

    public function test_failing_logger_does_not_throw(): void
    {
        $instance = $this->createInstance($logger = $this->createMock(LoggerInterface::class));
        $logger->expects($this->once())->method('log')->willThrowException(new RuntimeException('logger failed'));

        $instance->logError('some message');
    }

    private function createInstance(LoggerInterface $logger): object
    {
        return new class($logger) {
            use LogsToLoggerTrait {
                logDebug as public;
                logInfo as public;
                logNotice as public;
                logWarning as public;
                logError as public;
            }

            public function __construct(private readonly LoggerInterface $logger)
            {
            }

            #[\Override]
            protected function logger(): LoggerInterface
            {
                return $this->logger;
            }
        };
    }
}
