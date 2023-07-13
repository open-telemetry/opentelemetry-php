<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Propagation;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\API\Baggage\Propagation\BaggagePropagator;
use OpenTelemetry\API\LoggerHolder;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Context\Propagation\MultiTextMapPropagator;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Extension\Propagator\B3\B3Propagator;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Propagation\PropagatorFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @covers \OpenTelemetry\SDK\Propagation\PropagatorFactory
 */
class PropagatorFactoryTest extends TestCase
{
    use EnvironmentVariables;

    public function setUp(): void
    {
        LoggerHolder::set(new NullLogger());
    }

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    /**
     * @dataProvider propagatorsProvider
     * @psalm-suppress ArgumentTypeCoercion
     */
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
            [KnownValues::VALUE_B3_MULTI, B3Propagator::class],
            [KnownValues::VALUE_NONE, NoopTextMapPropagator::class],
            [sprintf('%s,%s', KnownValues::VALUE_B3, KnownValues::VALUE_BAGGAGE), MultiTextMapPropagator::class],
            ['unknown', NoopTextMapPropagator::class],
        ];
    }

    /**
     * @dataProvider unimplementedPropagatorProvider
     */
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
