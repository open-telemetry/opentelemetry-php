<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

use OpenTelemetry\SDK\Trace\ExporterFactory;
use OpenTelemetry\SDK\Trace\SamplerFactory;
use OpenTelemetry\SDK\Trace\SpanProcessorFactory;
use OpenTelemetry\SDK\Trace\TracerProviderFactory;
use PHPUnit\Framework\TestCase;

class TracerProviderFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function factory_createsTracer()
    {
        $exporterFactory = $this->createMock(ExporterFactory::class);
        $samplerFactory = $this->createMock(SamplerFactory::class);
        $spanProcessorFactory = $this->createMock(SpanProcessorFactory::class);

        $exporterFactory->expects($this->once())->method('fromConfig');
        $samplerFactory->expects($this->once())->method('fromConfig');
        $spanProcessorFactory->expects($this->once())->method('fromConfig');

        $factory = new TracerProviderFactory('test', $exporterFactory, $samplerFactory, $spanProcessorFactory);
        $factory->fromConfig((object) []);
    }
}
