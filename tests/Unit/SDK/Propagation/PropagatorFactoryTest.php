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

    #[\Override]
    public function setUp(): void
    {
        LoggerHolder::disable();
        Logging::disable();
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/configuration/sdk-environment-variables.md#enum
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
            ['Baggage', BaggagePropagator::class],
            ['TraceContext', TraceContextPropagator::class],
            ['NONE', NoopTextMapPropagator::class],
            [KnownValues::VALUE_TRACECONTEXT, TraceContextPropagator::class],
            [KnownValues::VALUE_B3, B3Propagator::class],
            [KnownValues::VALUE_B3_MULTI, B3Propagator::class],
            [KnownValues::VALUE_NONE, NoopTextMapPropagator::class],
            [sprintf('%s,%s', KnownValues::VALUE_B3, KnownValues::VALUE_BAGGAGE), MultiTextMapPropagator::class],
            ['unknown', NoopTextMapPropagator::class],
        ];
    }

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/configuration/sdk-environment-variables.md#enum
     */
    public function test_mixed_case_composite_propagator(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PROPAGATORS, 'TraceContext,Baggage');
        $propagator = (new PropagatorFactory())->create();

        $this->assertSame(['traceparent', 'tracestate', 'baggage'], $propagator->fields());
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
