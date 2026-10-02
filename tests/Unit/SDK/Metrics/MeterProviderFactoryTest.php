<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\AllExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\NoneExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MeterProviderFactory;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MeterProviderFactory::class)]
class MeterProviderFactoryTest extends TestCase
{
    use TestState;

    #[\Override]
    public function setUp(): void
    {
        Logging::disable();
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    #[DataProvider('exporterProvider')]
    public function test_create(string $exporter): void
    {
        $_SERVER[Variables::OTEL_METRICS_EXPORTER] = $exporter;
        $provider = (new MeterProviderFactory())->create();
        $this->assertInstanceOf(MeterInterface::class, $provider->getMeter('test'));
    }

    public static function exporterProvider(): array
    {
        return [
            'otlp' => [KnownValues::VALUE_OTLP],
            'none' => [KnownValues::VALUE_NONE],
            'unimplemented' => ['foo'],
        ];
    }

    /**
     * @param class-string<ExemplarFilterInterface> $expectedFilter
     */
    #[DataProvider('exemplarFilterProvider')]
    public function test_exemplar_filter(?string $filter, string $expectedFilter): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_METRICS_EXPORTER, KnownValues::VALUE_NONE);
        $this->setEnvironmentVariable(Variables::OTEL_METRICS_EXEMPLAR_FILTER, $filter);

        $provider = (new MeterProviderFactory())->create();
        self::assertInstanceOf(MeterProvider::class, $provider);

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('exemplarFilter');
        $property->setAccessible(true);

        self::assertInstanceOf($expectedFilter, $property->getValue($provider));
    }

    public static function exemplarFilterProvider(): iterable
    {
        yield 'default' => [null, WithSampledTraceExemplarFilter::class];
        yield 'always_on' => [KnownValues::VALUE_ALWAYS_ON, AllExemplarFilter::class];
        yield 'always_off' => [KnownValues::VALUE_ALWAYS_OFF, NoneExemplarFilter::class];
        yield 'trace_based' => [KnownValues::VALUE_TRACE_BASED, WithSampledTraceExemplarFilter::class];
        yield 'legacy_all' => [KnownValues::VALUE_ALL, AllExemplarFilter::class];
        yield 'legacy_none' => [KnownValues::VALUE_NONE, NoneExemplarFilter::class];
        yield 'legacy_with_sampled_trace' => [
            KnownValues::VALUE_WITH_SAMPLED_TRACE,
            WithSampledTraceExemplarFilter::class,
        ];
    }
}
