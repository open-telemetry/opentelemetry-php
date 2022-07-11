<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\SDK\Common\Log\LoggerHolder;
use OpenTelemetry\SDK\Trace\ExporterFactory;
use OpenTelemetry\SDK\Trace\SamplerFactory;
use OpenTelemetry\SDK\Trace\SpanProcessorFactory;
use OpenTelemetry\SDK\Trace\TracerProviderFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \OpenTelemetry\SDK\Trace\TracerProviderFactory
 * @coversDefaultClass \OpenTelemetry\SDK\Trace\TracerProviderFactory
 */
class TracerProviderFactoryTest extends TestCase
{
    private $logger;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        LoggerHolder::set($this->logger);
    }

    public function tearDown(): void
    {
        LoggerHolder::unset();
    }

    /**
     * @covers ::create
     * @covers ::__construct
     */
    public function test_factory_creates_tracer(): void
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
     * @covers ::create
     */
    public function test_factory_logs_warnings_and_continues(): void
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
        $this->logger->expects($this->atLeast(3))->method('log');

        $factory = new TracerProviderFactory('test', $exporterFactory, $samplerFactory, $spanProcessorFactory);
        $factory->create();
    }
}
