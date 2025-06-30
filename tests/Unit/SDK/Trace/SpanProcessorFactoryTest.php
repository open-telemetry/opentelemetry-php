<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\SDK\Metrics\MeterProviderInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SpanProcessorContext;
use OpenTelemetry\SDK\Trace\SpanProcessorFactory;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(SpanProcessorFactory::class)]
class SpanProcessorFactoryTest extends TestCase
{
    use TestState;

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    #[DataProvider('processorProvider')]
    public function test_span_processor_factory_create_span_processor_from_environment(string $processorName, string $expected): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_TRACES_PROCESSOR', $processorName);
        $factory = new SpanProcessorFactory();
        $context = new SpanProcessorContext(
            $this->createMock(MeterProviderInterface::class),
            $this->createMock(SpanExporterInterface::class),
            false,
        );
        $this->assertInstanceOf($expected, $factory->create($context));
    }

    public static function processorProvider(): array
    {
        return [
            'batch' => ['batch', BatchSpanProcessor::class],
            'simple' => ['simple', SimpleSpanProcessor::class],
            'noop' => ['noop', NoopSpanProcessor::class],
            'none' => ['none', NoopSpanProcessor::class],
        ];
    }

    public function test_span_processor_factory_default_span_processor(): void
    {
        $factory = new SpanProcessorFactory();
        $context = new SpanProcessorContext(
            $this->createMock(MeterProviderInterface::class),
            $this->createMock(SpanExporterInterface::class),
            false,
        );
        $this->assertInstanceOf(BatchSpanProcessor::class, $factory->create($context));
    }

    public function test_span_processor_factory_invalid_span_processor(): void
    {
        $this->setEnvironmentVariable('OTEL_PHP_TRACES_PROCESSOR', 'foo');
        $factory = new SpanProcessorFactory();
        $context = new SpanProcessorContext(
            $this->createMock(MeterProviderInterface::class),
            $this->createMock(SpanExporterInterface::class),
            false,
        );
        $this->expectException(RuntimeException::class);
        $factory->create($context);
    }
}
