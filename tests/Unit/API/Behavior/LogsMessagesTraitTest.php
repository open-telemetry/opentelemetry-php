<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Behavior;

use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\API\LoggerHolder;
use PHPUnit\Framework\Exception as PHPUnitFrameworkException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @covers \OpenTelemetry\API\Behavior\LogsMessagesTrait
 */
class LogsMessagesTraitTest extends TestCase
{
    // @var LoggerInterface|MockObject $logger
    protected MockObject $logger;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        LoggerHolder::set($this->logger);
    }

    public function tearDown(): void
    {
        LoggerHolder::unset();
    }

    public function test_defaults_to_trigger_error_with_warning(): void
    {
        $message = 'something went wrong';
        LoggerHolder::unset();
        $this->assertFalse(LoggerHolder::isSet());
        $instance = $this->createInstance();

        $this->expectException(PHPUnitFrameworkException::class);
        $this->expectExceptionMessageMatches(sprintf('/%s/', $message));
        $instance->run('logWarning', 'foo', ['exception' => new \Exception($message)]);
    }

    public function test_defaults_to_trigger_error_with_error(): void
    {
        $message = 'something went wrong';
        LoggerHolder::unset();
        $this->assertFalse(LoggerHolder::isSet());
        $instance = $this->createInstance();

        $this->expectException(PHPUnitFrameworkException::class);
        $this->expectExceptionMessageMatches(sprintf('/%s/', $message));
        $instance->run('logError', 'foo', ['exception' => new \Exception($message)]);
    }

    /**
     * @testdox Proxies logging methods through to logger
     * @dataProvider logLevelProvider
     */
    public function test_log_methods(string $method, string $expectedLogLevel): void
    {
        $instance = $this->createInstance();
        $this->logger->expects($this->once())->method('log')->with(
            $this->equalTo($expectedLogLevel),
            $this->equalTo('foo'),
        );
        $instance->run($method, 'foo');
    }

    public function logLevelProvider(): array
    {
        return [
            'debug' => ['logDebug', LogLevel::DEBUG],
            'info' => ['logInfo', LogLevel::INFO],
            'notice' => ['logNotice', LogLevel::NOTICE],
            'warning' => ['logWarning', LogLevel::WARNING],
            'error' => ['logError', LogLevel::ERROR],
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
