<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Logs;

use OpenTelemetry\API\Logs\NoopLogger;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactoryInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Config;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate\Name;
use OpenTelemetry\SDK\Common\InstrumentationScope\State;
use OpenTelemetry\SDK\Logs\Logger;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LoggerProviderBuilder;
use OpenTelemetry\SDK\Logs\LogRecordProcessorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress UndefinedInterfaceMethod
 * @psalm-suppress PossiblyUndefinedMethod
 */
#[CoversClass(LoggerProvider::class)]
class LoggerProviderTest extends TestCase
{
    /** @var LogRecordProcessorInterface&MockObject $processor */
    private LogRecordProcessorInterface $processor;
    private LoggerProvider $provider;
    /** @var Config&MockObject */
    private Config $config;

    public function setUp(): void
    {
        $this->processor = $this->createMock(LogRecordProcessorInterface::class);
        $instrumentationScopeFactory = $this->createMock(InstrumentationScopeFactoryInterface::class);
        $resource = $this->createMock(ResourceInfo::class);
        $this->config = $this->createMock(Config::class);
        $configurator = $this->createMock(Configurator::class);
        $configurator->method('getConfig')->willReturn($this->config);
        $this->provider = new LoggerProvider($this->processor, $instrumentationScopeFactory, $resource, $configurator);
    }

    public function test_get_logger(): void
    {
        $this->config->method('isEnabled')->willReturn(true);
        $logger = $this->provider->getLogger('name');
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function test_get_logger_after_shutdown(): void
    {
        $this->provider->shutdown();
        $logger = $this->provider->getLogger('name');
        $this->assertInstanceOf(NoopLogger::class, $logger);
    }

    public function test_get_logger_if_disabled(): void
    {
        $this->config->method('isEnabled')->willReturn(false);
        $logger = $this->provider->getLogger('name');
        $this->assertFalse($logger->isEnabled());
    }

    public function test_shutdown_calls_processor_shutdown(): void
    {
        $this->processor->expects($this->once())->method('shutdown');
        $this->provider->shutdown();
    }

    public function test_force_flush_calls_processor_force_flush(): void
    {
        $this->processor->expects($this->once())->method('forceFlush');
        $this->provider->forceFlush();
    }

    public function test_builder(): void
    {
        $this->assertInstanceOf(LoggerProviderBuilder::class, $this->provider->builder());
    }

    public function test_update_configurator_updates_loggers(): void
    {
        $lp = LoggerProvider::builder()->build();
        $this->assertInstanceOf(LoggerProvider::class, $lp);
        $one = $lp->getLogger('one');
        $two = $lp->getLogger('two');

        $this->assertTrue($one->isEnabled());
        $this->assertTrue($two->isEnabled());

        $lp->updateConfigurator(Configurator::builder()->addCondition(new Name('*'), State::DISABLED)->build());
        $this->assertFalse($one->isEnabled());
        $this->assertFalse($two->isEnabled());
    }
}
