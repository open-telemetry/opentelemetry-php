<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Propagation;

use OpenTelemetry\API\Baggage\Propagation\BaggagePropagator;
use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\LoggerHolder;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Context\Propagation\MultiTextMapPropagator;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Extension\Propagator\B3\B3Propagator;
use OpenTelemetry\Extension\Propagator\CloudTrace\CloudTracePropagator;
use OpenTelemetry\Extension\Propagator\Jaeger\JaegerBaggagePropagator;
use OpenTelemetry\Extension\Propagator\Jaeger\JaegerPropagator;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Propagation\PropagatorFactory;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(PropagatorFactory::class)]
class PropagatorFactoryTest extends TestCase
{
    use TestState;

    public function setUp(): void
    {
        LoggerHolder::disable();
        Logging::disable();
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    #[DataProvider('propagatorsProvider')]
    public function test_create(string $propagators, string $expected): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PROPAGATORS, $propagators);
        $propagator = (new PropagatorFactory())->create();
        $this->assertInstanceOf($expected, $propagator);
    }

    public static function propagatorsProvider(): array
    {
        return [
            [KnownValues::VALUE_BAGGAGE, BaggagePropagator::class],
            [KnownValues::VALUE_TRACECONTEXT, TraceContextPropagator::class],
            [KnownValues::VALUE_B3, B3Propagator::class],
            [KnownValues::VALUE_CLOUD_TRACE, CloudTracePropagator::class],
            [KnownValues::VALUE_CLOUD_TRACE_ONEWAY, CloudTracePropagator::class],
            [KnownValues::VALUE_JAEGER, JaegerPropagator::class],
            [KnownValues::VALUE_JAEGER_BAGGAGE, JaegerBaggagePropagator::class],
            [KnownValues::VALUE_B3_MULTI, B3Propagator::class],
            [KnownValues::VALUE_NONE, NoopTextMapPropagator::class],
            [sprintf('%s,%s', KnownValues::VALUE_B3, KnownValues::VALUE_BAGGAGE), MultiTextMapPropagator::class],
            ['unknown', NoopTextMapPropagator::class],
        ];
    }

    #[DataProvider('unimplementedPropagatorProvider')]
    public function test_unimplemented_propagators(string $propagator): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PROPAGATORS, $propagator);
        $propagator = (new PropagatorFactory())->create();
        $this->assertInstanceOf(NoopTextMapPropagator::class, $propagator);
    }

    public static function unimplementedPropagatorProvider(): array
    {
        return [
            [KnownValues::VALUE_OTTRACE],
            [KnownValues::VALUE_XRAY],
        ];
    }
}
