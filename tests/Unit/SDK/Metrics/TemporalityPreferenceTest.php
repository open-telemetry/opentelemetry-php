<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use OpenTelemetry\SDK\Metrics\TemporalityPreference;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TemporalityPreference::class)]
final class TemporalityPreferenceTest extends TestCase
{
    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/metrics/sdk_exporters/otlp.md#additional-environment-variable-configuration
     */
    #[DataProvider('preferenceProvider')]
    public function test_temporality_by_instrument_type(TemporalityPreference $preference, string $instrumentType, string $expected): void
    {
        $metric = $this->createStub(MetricMetadataInterface::class);
        $metric->method('instrumentType')->willReturn($instrumentType);

        $this->assertSame($expected, $preference->temporality($metric));
    }

    public static function preferenceProvider(): iterable
    {
        $cumulative = TemporalityPreference::cumulative();
        $delta = TemporalityPreference::delta();
        $lowMemory = TemporalityPreference::lowMemory();

        yield 'cumulative counter' => [$cumulative, InstrumentType::COUNTER, Temporality::CUMULATIVE];
        yield 'cumulative histogram' => [$cumulative, InstrumentType::HISTOGRAM, Temporality::CUMULATIVE];
        yield 'cumulative async counter' => [$cumulative, InstrumentType::ASYNCHRONOUS_COUNTER, Temporality::CUMULATIVE];

        yield 'delta counter' => [$delta, InstrumentType::COUNTER, Temporality::DELTA];
        yield 'delta histogram' => [$delta, InstrumentType::HISTOGRAM, Temporality::DELTA];
        yield 'delta async counter' => [$delta, InstrumentType::ASYNCHRONOUS_COUNTER, Temporality::DELTA];
        yield 'delta up-down counter' => [$delta, InstrumentType::UP_DOWN_COUNTER, Temporality::CUMULATIVE];
        yield 'delta async up-down counter' => [$delta, InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER, Temporality::CUMULATIVE];

        yield 'low memory counter' => [$lowMemory, InstrumentType::COUNTER, Temporality::DELTA];
        yield 'low memory histogram' => [$lowMemory, InstrumentType::HISTOGRAM, Temporality::DELTA];
        yield 'low memory up-down counter' => [$lowMemory, InstrumentType::UP_DOWN_COUNTER, Temporality::CUMULATIVE];
        yield 'low memory async counter' => [$lowMemory, InstrumentType::ASYNCHRONOUS_COUNTER, Temporality::CUMULATIVE];
        yield 'low memory async up-down counter' => [$lowMemory, InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER, Temporality::CUMULATIVE];
    }

    #[DataProvider('nameProvider')]
    public function test_from_name(string $name, TemporalityPreference $expected): void
    {
        $this->assertEquals($expected, TemporalityPreference::fromName($name));
    }

    public static function nameProvider(): iterable
    {
        yield 'cumulative' => ['cumulative', TemporalityPreference::cumulative()];
        yield 'delta' => ['delta', TemporalityPreference::delta()];
        yield 'environment variable low memory' => ['lowmemory', TemporalityPreference::lowMemory()];
        yield 'declarative config low memory' => ['low_memory', TemporalityPreference::lowMemory()];
        yield 'case insensitive' => ['Delta', TemporalityPreference::delta()];
    }

    public function test_from_name_rejects_unknown_name(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        TemporalityPreference::fromName('sometimes');
    }
}
