<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\API\Trace\NoopTracerProvider;
use OpenTelemetry\SDK\Trace\ExporterFactory;
use OpenTelemetry\SDK\Trace\SamplerFactory;
use OpenTelemetry\SDK\Trace\SpanProcessorFactory;
use OpenTelemetry\SDK\Trace\TracerProviderFactory;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Trace\TracerProviderFactory::class)]
class TracerProviderFactoryTest extends TestCase
{
    use TestState;

    /** @var LogWriterInterface&MockObject $logWriter */
    private LogWriterInterface $logWriter;

    public function setUp(): void
    {
        $this->logWriter = $this->createMock(LogWriterInterface::class);
        Logging::setLogWriter($this->logWriter);
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
        $this->logWriter->expects($this->atLeast(3))->method('write');

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
