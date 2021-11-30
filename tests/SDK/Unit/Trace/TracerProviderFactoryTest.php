<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

use OpenTelemetry\SDK\GlobalLoggerHolder;
use OpenTelemetry\SDK\Trace\ExporterFactory;
use OpenTelemetry\SDK\Trace\SamplerFactory;
use OpenTelemetry\SDK\Trace\SpanProcessorFactory;
use OpenTelemetry\SDK\Trace\TracerProviderFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass OpenTelemetry\SDK\Trace\TracerProviderFactory
 */
class TracerProviderFactoryTest extends TestCase
{
    private $logger;

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
     * @covers ::create
     * @covers ::__construct
     */
    public function factory_createsTracer()
    {
        $exporterFactory = $this->createMock(ExporterFactory::class);
        $samplerFactory = $this->createMock(SamplerFactory::class);
        $spanProcessorFactory = $this->createMock(SpanProcessorFactory::class);

        $exporterFactory->expects($this->once())->method('fromEnvironment');
        $samplerFactory->expects($this->once())->method('fromEnvironment');
        $spanProcessorFactory->expects($this->once())->method('fromEnvironment');

        $factory = new TracerProviderFactory('test', $exporterFactory, $samplerFactory, $spanProcessorFactory);
        $factory->create();
    }

    /**
     * @test
     * @covers ::create
     */
    public function factory_logsWarningsAndContinues(): void
    {
        $exporterFactory = $this->createMock(ExporterFactory::class);
        $samplerFactory = $this->createMock(SamplerFactory::class);
        $spanProcessorFactory = $this->createMock(SpanProcessorFactory::class);

        $exporterFactory->expects($this->once())
            ->method('fromEnvironment')
            ->willThrowException(new \InvalidArgumentException('foo'));
        $samplerFactory->expects($this->once())
            ->method('fromEnvironment')
            ->willThrowException(new \InvalidArgumentException('foo'));
        $spanProcessorFactory->expects($this->once())
            ->method('fromEnvironment')
            ->willThrowException(new \InvalidArgumentException('foo'));
        $this->logger->expects($this->atLeast(3))->method('warning');

        $factory = new TracerProviderFactory('test', $exporterFactory, $samplerFactory, $spanProcessorFactory);
        $factory->create();
    }
}
