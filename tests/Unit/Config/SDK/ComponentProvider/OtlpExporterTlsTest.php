<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\SDK\ComponentProvider;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\Logs\LogRecordExporterOtlpGrpc;
use OpenTelemetry\Config\SDK\ComponentProvider\Logs\LogRecordExporterOtlpHttp;
use OpenTelemetry\Config\SDK\ComponentProvider\Metrics\MetricExporterOtlpGrpc;
use OpenTelemetry\Config\SDK\ComponentProvider\Metrics\MetricExporterOtlpHttp;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanExporterOtlpGrpc;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanExporterOtlpHttp;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransport;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Registry;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function sprintf;

/**
 * The `tls` block of the OTLP exporters, as introduced by opentelemetry-configuration 1.0.
 *
 * Its three files map onto three distinct transport arguments, and until 1.0 the middle one was
 * wired up wrongly: the client certificate was passed as both `cert` and `key`, so an mTLS
 * private key never reached the transport. A config-shaped test cannot see that, since the
 * properties parse either way — so assert on what the transport is actually handed.
 */
#[CoversNothing]
final class OtlpExporterTlsTest extends TestCase
{
    #[\Override]
    public function setUp(): void
    {
        RecordingTransportFactory::reset();

        // Registry::transportFactory() instantiates the registered class rather than reusing a
        // registered instance, so the recorder has to keep its state statically.
        foreach (['http', 'grpc'] as $protocol) {
            Registry::registerTransportFactory($protocol, RecordingTransportFactory::class, true);
        }
    }

    #[\Override]
    public function tearDown(): void
    {
        // the Registry is global, so put the real factories back for everyone else
        Registry::registerTransportFactory('http', OtlpHttpTransportFactory::class, true);
        Registry::registerTransportFactory('grpc', GrpcTransportFactory::class, true);

        RecordingTransportFactory::reset();
    }

    /**
     * @param ComponentProvider<object> $provider
     */
    #[DataProvider('exporterProvider')]
    public function test_tls_files_are_passed_to_transport(ComponentProvider $provider, string $endpoint): void
    {
        $provider->createPlugin([
            'endpoint' => $endpoint,
            'tls' => [
                'ca_file' => '/app/ca.pem',
                'cert_file' => '/app/client.pem',
                'key_file' => '/app/client.key',
                'insecure' => false,
            ],
            'headers' => [],
            'headers_list' => null,
            'compression' => null,
            'timeout' => 10000,
            'encoding' => 'protobuf',
            'temporality_preference' => 'cumulative',
            'default_histogram_aggregation' => 'explicit_bucket_histogram',
        ], new Context());

        $this->assertSame('/app/ca.pem', RecordingTransportFactory::$cacert);
        $this->assertSame('/app/client.pem', RecordingTransportFactory::$cert);
        $this->assertSame('/app/client.key', RecordingTransportFactory::$key, 'key_file, not cert_file');
    }

    /**
     * @param ComponentProvider<object> $provider
     */
    #[DataProvider('exporterProvider')]
    public function test_omitted_tls_files_are_passed_as_null(ComponentProvider $provider, string $endpoint): void
    {
        $provider->createPlugin([
            'endpoint' => $endpoint,
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
            'temporality_preference' => 'cumulative',
            'default_histogram_aggregation' => 'explicit_bucket_histogram',
        ], new Context());

        $this->assertNull(RecordingTransportFactory::$cacert);
        $this->assertNull(RecordingTransportFactory::$cert);
        $this->assertNull(RecordingTransportFactory::$key);
        $this->assertTrue(RecordingTransportFactory::$called, 'a transport was created');
    }

    /**
     * @param ComponentProvider<object> $provider
     */
    #[DataProvider('grpcInsecureProvider')]
    public function test_insecure_is_applied_as_endpoint_scheme(
        ComponentProvider $provider,
        string $endpoint,
        ?bool $insecure,
        string $expected,
    ): void {
        $provider->createPlugin([
            'endpoint' => $endpoint,
            'tls' => [
                'ca_file' => null,
                'cert_file' => null,
                'key_file' => null,
                'insecure' => $insecure,
            ],
            'headers' => [],
            'headers_list' => null,
            'compression' => null,
            'timeout' => 10000,
            'temporality_preference' => 'cumulative',
            'default_histogram_aggregation' => 'explicit_bucket_histogram',
        ], new Context());

        $this->assertSame($expected, RecordingTransportFactory::$endpoint);
    }

    public static function grpcInsecureProvider(): iterable
    {
        $signals = [
            'traces' => [new SpanExporterOtlpGrpc(), '/opentelemetry.proto.collector.trace.v1.TraceService/Export'],
            'metrics' => [new MetricExporterOtlpGrpc(), '/opentelemetry.proto.collector.metrics.v1.MetricsService/Export'],
            'logs' => [new LogRecordExporterOtlpGrpc(), '/opentelemetry.proto.collector.logs.v1.LogsService/Export'],
        ];

        foreach ($signals as $signal => [$provider, $method]) {
            yield sprintf('%s insecure true', $signal) => [$provider, 'localhost:4317', true, 'http://localhost:4317' . $method];
            yield sprintf('%s insecure false', $signal) => [$provider, 'localhost:4317', false, 'https://localhost:4317' . $method];
            yield sprintf('%s insecure omitted defaults to secure', $signal) => [$provider, 'localhost:4317', null, 'https://localhost:4317' . $method];
            yield sprintf('%s explicit https kept', $signal) => [$provider, 'https://localhost:4317', true, 'https://localhost:4317' . $method];
            yield sprintf('%s explicit http kept', $signal) => [$provider, 'http://localhost:4317', false, 'http://localhost:4317' . $method];
        }
    }

    public static function exporterProvider(): iterable
    {
        yield 'traces otlp_http' => [new SpanExporterOtlpHttp(), 'http://localhost:4318/v1/traces'];
        yield 'traces otlp_grpc' => [new SpanExporterOtlpGrpc(), 'http://localhost:4317'];
        yield 'metrics otlp_http' => [new MetricExporterOtlpHttp(), 'http://localhost:4318/v1/metrics'];
        yield 'metrics otlp_grpc' => [new MetricExporterOtlpGrpc(), 'http://localhost:4317'];
        yield 'logs otlp_http' => [new LogRecordExporterOtlpHttp(), 'http://localhost:4318/v1/logs'];
        yield 'logs otlp_grpc' => [new LogRecordExporterOtlpGrpc(), 'http://localhost:4317'];
    }
}

/**
 * @internal
 */
final class RecordingTransportFactory implements TransportFactoryInterface
{
    public static ?string $endpoint = null;
    public static ?string $cacert = null;
    public static ?string $cert = null;
    public static ?string $key = null;
    public static bool $called = false;

    public static function reset(): void
    {
        self::$endpoint = null;
        self::$cacert = null;
        self::$cert = null;
        self::$key = null;
        self::$called = false;
    }

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
        self::$endpoint = $endpoint;
        self::$cacert = $cacert;
        self::$cert = $cert;
        self::$key = $key;
        self::$called = true;

        // a null stream is enough: the tests assert on what was recorded above, and never export
        return new StreamTransport(null, $contentType);
    }
}
