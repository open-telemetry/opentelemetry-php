<?php

declare(strict_types=1);

namespacfinal e OpenTelemetry\Tests\Unit\SDK\Trace\SpanProcessor;

use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessorBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BatchSpanProcessorBuilder::class)]
class BatchSpanProcessorBuilderTest extends TestCase
{
    public function test_build(): void
    {
        $exporter = $this->createMock(SpanExporterInterface::class);
        $meterProvider = $this->createMock(MeterProviderInterface::class);
        $meter = $this->createMock(MeterInterface::class);
        $meterProvider->expects($this->once())->method('getMeter')->willReturn($meter);
        $processor = BatchSpanProcessor::builder($exporter)
            ->setMeterProvider($meterProvider)
            ->build();
        $reflection = new \ReflectionClass($processor);
        $property = $reflection->getProperty('exporter');
        $property->setAccessible(true);

        $this->assertSame($exporter, $property->getValue($processor));
    }
}
