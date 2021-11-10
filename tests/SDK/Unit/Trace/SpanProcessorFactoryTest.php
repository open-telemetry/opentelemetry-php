<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Unit\Trace;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Exception;
use OpenTelemetry\SDK\Trace\SpanProcessorFactory;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
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
    public function spanProcessorFactory_createSpanProcessorFromEnvironment(string $processor)
    {
        $this->setEnvironmentVariable('OTEL_TRACES_PROCESSOR', $processor);
        $factory = new SpanProcessorFactory();
        $this->assertInstanceOf(SpanProcessorInterface::class, $factory->fromEnvironment());
    }

    public function processorProvider()
    {
        return [
            'batch' => ['batch'],
            'simple' => ['simple'],
            'noop' => ['noop'],
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
        $exporter = $this->createMock(\OpenTelemetry\SDK\Trace\SpanExporterInterface::class);
        $this->expectException(Exception::class);
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
