<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\ComponentProvider;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\Metrics\MetricExporterConsole;
use OpenTelemetry\Config\SDK\ComponentProvider\Metrics\MetricExporterOtlpFile;
use OpenTelemetry\Config\SDK\ComponentProvider\Metrics\MetricExporterOtlpGrpc;
use OpenTelemetry\Config\SDK\ComponentProvider\Metrics\MetricExporterOtlpHttp;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransport;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Metrics\AggregationTemporalitySelectorInterface;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\InstrumentType;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use OpenTelemetry\SDK\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function sprintf;

#[CoversClass(MetricExporterConsole::class)]
#[CoversClass(MetricExporterOtlpFile::class)]
#[CoversClass(MetricExporterOtlpGrpc::class)]
#[CoversClass(MetricExporterOtlpHttp::class)]
final class MetricExporterTemporalityTest extends TestCase
{
    #[\Override]
    public function setUp(): void
    {
        foreach (['http', 'grpc'] as $protocol) {
            Registry::registerTransportFactory($protocol, NullTransportFactory::class, true);
        }
    }

    #[\Override]
    public function tearDown(): void
    {
        Registry::registerTransportFactory('http', OtlpHttpTransportFactory::class, true);
        Registry::registerTransportFactory('grpc', GrpcTransportFactory::class, true);
    }

    /**
     * @param ComponentProvider<MetricExporterInterface> $provider
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.62.0/specification/metrics/sdk_exporters/otlp.md#additional-environment-variable-configuration
     */
    #[DataProvider('temporalityProvider')]
    public function test_temporality_preference_selects_by_instrument_type(
        ComponentProvider $provider,
        string $preference,
        string $instrumentType,
        string $expected,
    ): void {
        $exporter = $provider->createPlugin([
            'endpoint' => 'http://localhost:4318/v1/metrics',
            'output_stream' => 'stdout',
            'tls' => [
                'ca_file' => null,
                'cert_file' => null,
                'key_file' => null,
                'insecure' => null,
            ],
            'headers' => [],
            'headers_list' => null,
            'compression' => null,
            'timeout' => 10000,
            'encoding' => 'protobuf',
            'temporality_preference' => $preference,
            'default_histogram_aggregation' => 'explicit_bucket_histogram',
        ], new Context());

        $metric = $this->createStub(MetricMetadataInterface::class);
        $metric->method('instrumentType')->willReturn($instrumentType);
        $metric->method('temporality')->willReturn(Temporality::DELTA);

        $this->assertInstanceOf(AggregationTemporalitySelectorInterface::class, $exporter);
        $this->assertSame($expected, $exporter->temporality($metric));
    }

    public static function temporalityProvider(): iterable
    {
        $providers = [
            'console' => new MetricExporterConsole(),
            'otlp_file' => new MetricExporterOtlpFile(),
            'otlp_grpc' => new MetricExporterOtlpGrpc(),
            'otlp_http' => new MetricExporterOtlpHttp(),
        ];

        foreach ($providers as $name => $provider) {
            yield sprintf('%s cumulative counter', $name) => [$provider, 'cumulative', InstrumentType::COUNTER, Temporality::CUMULATIVE];
            yield sprintf('%s delta counter', $name) => [$provider, 'delta', InstrumentType::COUNTER, Temporality::DELTA];
            yield sprintf('%s delta up down counter', $name) => [$provider, 'delta', InstrumentType::UP_DOWN_COUNTER, Temporality::CUMULATIVE];
            yield sprintf('%s low memory histogram', $name) => [$provider, 'low_memory', InstrumentType::HISTOGRAM, Temporality::DELTA];
            yield sprintf('%s low memory asynchronous counter', $name) => [$provider, 'low_memory', InstrumentType::ASYNCHRONOUS_COUNTER, Temporality::CUMULATIVE];
        }
    }
}

/**
 * @internal
 */
final class NullTransportFactory implements TransportFactoryInterface
{
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
        return new StreamTransport(null, $contentType);
    }
}
