<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Otlp;

use OpenTelemetry\Contrib\Otlp\StdoutMetricExporterFactory;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Metrics\AggregationTemporalitySelectorInterface;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(StdoutMetricExporterFactory::class)]
class StdoutMetricExporterFactoryTest extends TestCase
{
    use TestState;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/protocol/file-exporter.md#exporter-configuration
     */
    #[DataProvider('temporalityProvider')]
    public function test_create_with_temporality(?string $preference, string $instrumentType, string $expected): void
    {
        if ($preference !== null) {
            $this->setEnvironmentVariable(Variables::OTEL_EXPORTER_OTLP_METRICS_TEMPORALITY_PREFERENCE, $preference);
        }
        $exporter = (new StdoutMetricExporterFactory())->create();
        $metric = $this->createStub(MetricMetadataInterface::class);
        $metric->method('instrumentType')->willReturn($instrumentType);
        $metric->method('temporality')->willReturn(Temporality::DELTA);

        $this->assertInstanceOf(AggregationTemporalitySelectorInterface::class, $exporter);
        $this->assertSame($expected, $exporter->temporality($metric));
    }

    public static function temporalityProvider(): array
    {
        return [
            'default' => [null, InstrumentType::COUNTER, Temporality::CUMULATIVE],
            'cumulative' => ['cumulative', InstrumentType::COUNTER, Temporality::CUMULATIVE],
            'delta counter' => ['delta', InstrumentType::COUNTER, Temporality::DELTA],
            'delta up/down counter' => ['delta', InstrumentType::UP_DOWN_COUNTER, Temporality::CUMULATIVE],
            'lowmemory asynchronous counter' => ['lowmemory', InstrumentType::ASYNCHRONOUS_COUNTER, Temporality::CUMULATIVE],
        ];
    }
}
