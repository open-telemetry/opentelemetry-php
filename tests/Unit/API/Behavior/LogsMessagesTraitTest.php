<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Behavior;

use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

#[CoversClass(LogsMessagesTrait::class)]
class LogsMessagesTraitTest extends TestCase
{
    use TestState;

    protected MockObject $writer;

    public function setUp(): void
    {
        Logging::reset();
        $this->writer = $this->createMock(LogWriterInterface::class);
        Logging::setLogWriter($this->writer);
    }

    #[DataProvider('logProvider')]
    public function test_log(string $method, string $expectedLevel): void
    {
        $instance = $this->createInstance();

        $this->writer->expects($this->once())->method('write')->with(
            $this->equalTo($expectedLevel),
            $this->stringContains('foo'),
            $this->anything()
        );

        $instance->run($method, 'foo', ['exception' => new \Exception('bang')]);
    }

    public static function logProvider(): array
    {
        return [
            ['logWarning', 'warning'],
            ['logError', 'error'],
        ];
    }

    #[DataProvider('logLevelProvider')]
    #[TestDox('Proxies logging methods through to logger')]
    public function test_log_methods(string $method, string $expectedLogLevel): void
    {
        $instance = $this->createInstance();
        $this->writer->expects($this->once())->method('write')->with(
            $this->equalTo($expectedLogLevel),
            $this->equalTo('foo'),
        );
        $instance->run($method, 'foo');
    }

    public static function logLevelProvider(): array
    {
        return [
            'debug' => ['logDebug', LogLevel::DEBUG],
            'info' => ['logInfo', LogLevel::INFO],
            'notice' => ['logNotice', LogLevel::NOTICE],
            'warning' => ['logWarning', LogLevel::WARNING],
            'error' => ['logError', LogLevel::ERROR],
        ];
    }

    #[DataProvider('otelLogLevelProvider')]
    public function test_logging_respects_configured_otel_log_level(string $otelLogLevel, string $method, bool $expected): void
    {
        $this->setEnvironmentVariable('OTEL_LOG_LEVEL', $otelLogLevel);
        $instance = $this->createInstance();
        if ($expected) {
            $this->writer->expects($this->once())->method('write');
        } else {
            $this->writer->expects($this->never())->method('write');
        }
        $instance->run($method, 'test message');
    }

    public static function otelLogLevelProvider(): array
    {
        return [
            ['warning', 'logWarning', true],
            ['warning', 'logError', true],
            ['warning', 'logInfo', false],
            ['none', 'logError', false],
        ];
    }

    private function createInstance(): object
    {
        return new class() {
            use LogsMessagesTrait;
            //accessor for protected trait methods
            public function run(string $method, string $message, array $context = []): void
            {
                $this->{$method}($message, $context);
            }
        };
    }
}
