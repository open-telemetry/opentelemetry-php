<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\API\LoggerHolder;
use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\SDK\Trace\ExporterFactory;
use OpenTelemetry\SDK\Trace\SamplerFactory;
use OpenTelemetry\SDK\Trace\SpanProcessorFactory;
use OpenTelemetry\SDK\Trace\TracerProviderFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \OpenTelemetry\SDK\Trace\TracerProviderFactory
 */
class TracerProviderFactoryTest extends TestCase
{
    use EnvironmentVariables;

    private $logger;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        LoggerHolder::set($this->logger);
    }

    public function tearDown(): void
    {
        LoggerHolder::unset();
        $this->restoreEnvironmentVariables();
    }

    public function test_factory_creates_tracer(): void
    {
        $exporterFactory = $this->createMock(ExporterFactory::class);
        $samplerFactory = $this->createMock(SamplerFactory::class);
        $spanProcessorFactory = $this->createMock(SpanProcessorFactory::class);

        $exporterFactory->expects($this->once())->method('create');
        $samplerFactory->expects($this->once())->method('create');
        $spanProcessorFactory->expects($this->once())->method('create');

        $factory = new TracerProviderFactory($exporterFactory, $samplerFactory, $spanProcessorFactory);
        $factory->create();
    }

    public function test_factory_logs_warnings_and_continues(): void
    {
        $exporterFactory = $this->createMock(ExporterFactory::class);
        $samplerFactory = $this->createMock(SamplerFactory::class);
        $spanProcessorFactory = $this->createMock(SpanProcessorFactory::class);

        $exporterFactory->expects($this->once())
            ->method('create')
            ->willThrowException(new \InvalidArgumentException('foo'));
        $samplerFactory->expects($this->once())
            ->method('create')
            ->willThrowException(new \InvalidArgumentException('foo'));
        $spanProcessorFactory->expects($this->once())
            ->method('create')
            ->willThrowException(new \InvalidArgumentException('foo'));
        $this->logger->expects($this->atLeast(3))->method('log');

        $factory = new TracerProviderFactory($exporterFactory, $samplerFactory, $spanProcessorFactory);
        $factory->create();
    }

    public function test_can_be_disabled(): void
    {
        $this->setEnvironmentVariable('OTEL_SDK_DISABLED', 'true');
        $factory = new TracerProviderFactory();
        $this->assertInstanceOf(NoopTracerProvider::class, $factory->create());
    }
}
