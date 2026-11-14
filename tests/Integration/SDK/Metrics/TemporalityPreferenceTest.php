<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK\Metrics;

use ArrayObject;
use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use Opentelemetry\Proto\Metrics\V1\AggregationTemporality;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderFactory;
use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
final class TemporalityPreferenceTest extends TestCase
{
    use TestState;

    private const DELTA = AggregationTemporality::AGGREGATION_TEMPORALITY_DELTA;
    private const CUMULATIVE = AggregationTemporality::AGGREGATION_TEMPORALITY_CUMULATIVE;

    #[\Override]
    protected function setUp(): void
    {
        RecordingTransportFactory::$payloads = new ArrayObject();
        Registry::registerTransportFactory('http', RecordingTransportFactory::class, true);
    }

    #[\Override]
    protected function tearDown(): void
    {
        Registry::registerTransportFactory('http', OtlpHttpTransportFactory::class, true);
    }

    /**
     * @param array<string, int|null> $expected temporality by metric name, null for a gauge
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/metrics/sdk_exporters/otlp.md#additional-environment-variable-configuration
     */
    #[DataProvider('preferenceProvider')]
    public function test_preference_selects_temporality_by_instrument_kind(string $preference, array $expected): void
    {
        $this->setEnvironmentVariable('OTEL_METRICS_EXPORTER', 'otlp');
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_PROTOCOL', 'http/json');
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_METRICS_TEMPORALITY_PREFERENCE', $preference);

        $meterProvider = (new MeterProviderFactory())->create(ResourceInfoFactory::emptyResource());
        $meter = $meterProvider->getMeter('test');
        $meter->createCounter('counter')->add(1);
        $meter->createUpDownCounter('up_down_counter')->add(1);
        $meter->createHistogram('histogram')->record(1);
        $meter->createGauge('gauge')->record(1);
        $meter->createObservableCounter('observable_counter', callbacks: fn (ObserverInterface $o) => $o->observe(1));
        $meter->createObservableUpDownCounter('observable_up_down_counter', callbacks: fn (ObserverInterface $o) => $o->observe(1));
        $meter->createObservableGauge('observable_gauge', callbacks: fn (ObserverInterface $o) => $o->observe(1));
        $meterProvider->forceFlush();

        $this->assertCount(1, RecordingTransportFactory::$payloads);
        $data = json_decode(RecordingTransportFactory::$payloads[0], true, flags: JSON_THROW_ON_ERROR);

        $actual = [];
        foreach ($data['resourceMetrics'][0]['scopeMetrics'][0]['metrics'] as $metric) {
            $points = $metric['sum'] ?? $metric['histogram'] ?? $metric['gauge'];
            $actual[$metric['name']] = $points['aggregationTemporality'] ?? null;
        }
        ksort($actual);
        ksort($expected);

        $this->assertSame($expected, $actual);
    }

    public static function preferenceProvider(): iterable
    {
        yield 'cumulative' => ['cumulative', [
            'counter' => self::CUMULATIVE,
            'up_down_counter' => self::CUMULATIVE,
            'histogram' => self::CUMULATIVE,
            'gauge' => null,
            'observable_counter' => self::CUMULATIVE,
            'observable_up_down_counter' => self::CUMULATIVE,
            'observable_gauge' => null,
        ]];
        yield 'delta' => ['delta', [
            'counter' => self::DELTA,
            'up_down_counter' => self::CUMULATIVE,
            'histogram' => self::DELTA,
            'gauge' => null,
            'observable_counter' => self::DELTA,
            'observable_up_down_counter' => self::CUMULATIVE,
            'observable_gauge' => null,
        ]];
        yield 'lowmemory' => ['lowmemory', [
            'counter' => self::DELTA,
            'up_down_counter' => self::CUMULATIVE,
            'histogram' => self::DELTA,
            'gauge' => null,
            'observable_counter' => self::CUMULATIVE,
            'observable_up_down_counter' => self::CUMULATIVE,
            'observable_gauge' => null,
        ]];
    }
}

/**
 * @internal
 */
final class RecordingTransportFactory implements TransportFactoryInterface
{
    /** @var ArrayObject<int, string> */
    public static ArrayObject $payloads;

    #[\Override]
    public function create(
        string $endpoint,
        string $contentType,
        array $headers = [],
        $compression = null,
        float $timeout = 10.,
        int $retryDelay = 100,
        int $maxRetries = 3,
        ?string $cacert = null,
        ?string $cert = null,
        ?string $key = null,
    ): TransportInterface {
        return new RecordingTransport($contentType, self::$payloads);
    }
}

/**
 * @internal
 * @template-implements TransportInterface<string>
 */
final class RecordingTransport implements TransportInterface
{
    /**
     * @param ArrayObject<int, string> $payloads
     */
    public function __construct(
        private readonly string $contentType,
        private readonly ArrayObject $payloads,
    ) {
    }

    #[\Override]
    public function contentType(): string
    {
        return $this->contentType;
    }

    #[\Override]
    public function send(string $payload, ?CancellationInterface $cancellation = null): FutureInterface
    {
        $this->payloads->append($payload);

        return new CompletedFuture(null);
    }

    #[\Override]
    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }

    #[\Override]
    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }
}
