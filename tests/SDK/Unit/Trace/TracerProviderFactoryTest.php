<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

use OpenTelemetry\SDK\Trace\ExporterFactory;
use OpenTelemetry\SDK\Trace\SamplerFactory;
use OpenTelemetry\SDK\Trace\SpanProcessorFactory;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\TracerProviderFactory;
use PHPUnit\Framework\TestCase;

/**
 * @backupGlobals enabled
 */
class TracerProviderFactoryTest extends TestCase
{
    private $factory;
    private $exporterFactory;
    private $samplerFactory;
    private $spanProcessorFactory;

    public function setUp(): void
    {
        $this->exporterFactory = $this->createMock(ExporterFactory::class);
        $this->samplerFactory = $this->createMock(SamplerFactory::class);
        $this->spanProcessorFactory = $this->createMock(SpanProcessorFactory::class);
        $this->factory = new TracerProviderFactory('test', $this->exporterFactory, $this->samplerFactory, $this->spanProcessorFactory);
    }

    /**
     * @test
     */
    public function factory_constructor()
    {
        $factory = new TracerProviderFactory('foo');
        $this->assertInstanceOf(TracerProviderFactory::class, $factory);
    }

    /**
     * @test
     */
    public function factory_createsExporter()
    {
        $tracer = $this->factory->create();
        $this->assertInstanceOf(TracerProvider::class, $tracer);
    }
}
