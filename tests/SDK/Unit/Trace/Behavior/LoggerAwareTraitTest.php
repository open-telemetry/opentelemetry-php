<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace\Behavior;

use OpenTelemetry\SDK\Trace\Behavior\LoggerAwareTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LoggerAwareTraitTest extends TestCase
{
    /**
     * @test
     * @testdox Injects logger into classes implementing LoggerAwareInterface
     */
    public function testInjectsLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $instance = $this->createInstance()->setLogger($logger);
        $loggerAware = $this->createMock(LoggerAwareInterface::class);
        $loggerAware->expects($this->once())->method('setLogger')->with($logger);

        $instance->addLogger($loggerAware);
    }

    /**
     * @test
     * @testdox Proxies logging methods through to logger
     * @dataProvider logLevelProvider
     */
    public function testLogMethods(string $method, string $expectedLogLevel): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $instance = $this->createInstance()->setLogger($logger);
        $logger->expects($this->once())->method('log')->with(
            $this->equalTo($expectedLogLevel),
            $this->equalTo('foo'),
        );
        $instance->run($method, 'foo');
    }

    public function logLevelProvider(): array
    {
        return [
            ['logDebug', LogLevel::DEBUG],
            ['logInfo', LogLevel::INFO],
            ['logNotice', LogLevel::NOTICE],
            ['logWarning', LogLevel::WARNING],
            ['logError', LogLevel::ERROR],
        ];
    }

    private function createInstance(): object
    {
        return new class() {
            use LoggerAwareTrait;
            public function addLogger(object $object): object
            {
                $this->injectLogger($object);

                return $object;
            }
            public function run(string $method, string $message): void
            {
                $this->{$method}($message);
            }
        };
    }
}
