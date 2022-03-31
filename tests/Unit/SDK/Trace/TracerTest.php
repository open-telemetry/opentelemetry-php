<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationLibrary;
use OpenTelemetry\SDK\Trace\Tracer;
use OpenTelemetry\SDK\Trace\TracerSharedState;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Trace\Tracer
 */
class TracerTest extends TestCase
{
    private Tracer $tracer;
    private TracerSharedState $tracerSharedState;
    private InstrumentationLibrary $instrumentationLibrary;

    protected function setUp(): void
    {
        $this->tracerSharedState = $this->createMock(TracerSharedState::class);
        $this->instrumentationLibrary = $this->createMock(InstrumentationLibrary::class);
        $this->tracer = (new Tracer($this->tracerSharedState, $this->instrumentationLibrary));
    }

    /**
     * @dataProvider nameProvider
     * @param non-empty-string $name
     */
    public function test_span_builder(string $name, string $expected): void
    {
        $spanBuilder = $this->tracer->spanBuilder($name);
        $reflection = new \ReflectionClass($spanBuilder);
        $property = $reflection->getProperty('spanName');
        $property->setAccessible(true);

        $this->assertSame($expected, $property->getValue($spanBuilder));
    }

    public function nameProvider(): array
    {
        return [
            'valid name' => ['name', 'name'],
            'invalid name uses fallback' => [' ', Tracer::FALLBACK_SPAN_NAME],
        ];
    }

    /**
     */
    public function test_get_instrumentation_library(): void
    {
        $this->assertSame($this->instrumentationLibrary, $this->tracer->getInstrumentationLibrary());
    }
}
