<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use InvalidArgumentException;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessorFactory;
use PHPUnit\Framework\TestCase;

class SpanProcessorFactoryTest extends TestCase
{
    use EnvironmentVariables;

    protected function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    /**
     * @test
     * @dataProvider processorProvider
     */
    public function spanProcessorFactory_createSpanProcessorFromEnvironment(string $processorName, string $expected)
    {
        $this->setEnvironmentVariable('OTEL_TRACES_PROCESSOR', $processorName);
        $factory = new SpanProcessorFactory();
        $this->assertInstanceOf($expected, $factory->fromEnvironment());
    }

    public function processorProvider()
    {
        return [
            'batch' => ['batch', BatchSpanProcessor::class],
            'simple' => ['simple', SimpleSpanProcessor::class],
            'noop' => ['noop', NoopSpanProcessor::class],
        ];
    }
    /**
     * @test
     * @dataProvider invalidProcessorProvider
     */
    public function spanProcessorFactory_invalidSpanProcessor(?string $processor)
    {
        $this->setEnvironmentVariable('OTEL_TRACES_PROCESSOR', $processor);
        $factory = new SpanProcessorFactory();
        $exporter = $this->createMock(SpanExporterInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $factory->fromEnvironment($exporter);
    }
    public function invalidProcessorProvider()
    {
        return [
            'not set' => [null],
            'invalid processor' => ['foo'],
        ];
    }
}
