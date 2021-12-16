<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Behavior;

use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\GlobalLoggerHolder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LogsMessagesTraitTest extends TestCase
{
    // @var LoggerInterface|MockObject $logger
    protected MockObject $logger;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        GlobalLoggerHolder::set($this->logger);
    }

    public function tearDown(): void
    {
        GlobalLoggerHolder::unset();
    }

    /**
     * @test
     * @testdox Proxies logging methods through to logger
     * @dataProvider logLevelProvider
     */
    public function testLogMethods(string $method, string $expectedLogLevel): void
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
            public function run(string $method, string $message): void
            {
                $this->{$method}($message);
            }
        };
    }
}
