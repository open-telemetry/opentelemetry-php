<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\API\Trace\NoopSpanBuilder;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScope;
use OpenTelemetry\SDK\Trace\Tracer;
use OpenTelemetry\SDK\Trace\TracerSharedState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(Tracer::class)]
class TracerTest extends TestCase
{
    private Tracer $tracer;
    private TracerSharedState $tracerSharedState;
    private InstrumentationScope $instrumentationScope;

    protected function setUp(): void
    {
        $this->tracerSharedState = $this->createMock(TracerSharedState::class);
        $this->instrumentationScope = $this->createMock(InstrumentationScope::class);
        $this->tracer = (new Tracer($this->tracerSharedState, $this->instrumentationScope));
    }

    /**
     * @param non-empty-string $name
     */
    #[DataProvider('nameProvider')]
    #[Group('trace-compliance')]
    public function test_span_builder(string $name, string $expected): void
    {
        $spanBuilder = $this->tracer->spanBuilder($name);
        $reflection = new \ReflectionClass($spanBuilder);
        $property = $reflection->getProperty('spanName');
        $property->setAccessible(true);

        $this->assertSame($expected, $property->getValue($spanBuilder));
    }

    public static function nameProvider(): array
    {
        return [
            'valid name' => ['name', 'name'],
            'invalid name uses fallback' => [' ', Tracer::FALLBACK_SPAN_NAME],
        ];
    }

    public function test_get_instrumentation_scope(): void
    {
        $this->assertSame($this->instrumentationScope, $this->tracer->getInstrumentationScope());
    }

    /**
     * @psalm-suppress UndefinedMethod
     */
    public function test_returns_noop_span_builder_if_shared_state_is_shutdown(): void
    {
        $this->tracerSharedState->method('hasShutdown')->willReturn(true); //@phpstan-ignore-line
        $this->assertInstanceOf(NoopSpanBuilder::class, $this->tracer->spanBuilder('foo'));
    }

    public function test_enabled(): void
    {
        $this->assertTrue($this->tracer->enabled());
    }
}
