<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

use OpenTelemetry\SDK\Trace\ExporterFactory;
use OpenTelemetry\SDK\Trace\SamplerFactory;
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

        $exporterFactory->expects($this->once())->method('fromEnvironment');
        $samplerFactory->expects($this->once())->method('fromEnvironment');

        $factory = new TracerProviderFactory('test', $exporterFactory, $samplerFactory);
        $factory->create();
    }
}
