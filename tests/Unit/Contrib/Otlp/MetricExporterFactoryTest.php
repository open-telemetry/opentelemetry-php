<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Otlp;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\Config\KnownValues;
use OpenTelemetry\Config\Variables;
use OpenTelemetry\Contrib\Otlp\MetricExporterFactory;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Metrics\AggregationTemporalitySelectorInterface;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Contrib\Otlp\MetricExporterFactory
 * @psalm-suppress UndefinedInterfaceMethod
 */
class MetricExporterFactoryTest extends TestCase
{
    use EnvironmentVariables;
    private TransportFactoryInterface $transportFactory;
    private TransportInterface $transport;

    public function setUp(): void
    {
        $this->transportFactory = $this->createMock(TransportFactoryInterface::class);
        $this->transport = $this->createMock(TransportInterface::class);
    }

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    public function test_unknown_protocol_exception(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->setEnvironmentVariable(Variables::OTEL_EXPORTER_OTLP_PROTOCOL, 'foo');
        $factory = new MetricExporterFactory();
        $factory->create();
    }

    /**
     * @dataProvider temporalityProvider
     */
    public function test_create_with_temporality(array $env, ?string $expected): void
    {
        // @phpstan-ignore-next-line
        $this->transportFactory->method('create')->willReturn($this->transport);
        // @phpstan-ignore-next-line
        $this->transport->method('contentType')->willReturn('application/json');

        foreach ($env as $k => $v) {
            $this->setEnvironmentVariable($k, $v);
        }
        $factory = new MetricExporterFactory($this->transportFactory);
        $exporter = $factory->create();

        $this->assertInstanceOf(AggregationTemporalitySelectorInterface::class, $exporter);
        $this->assertSame($expected, $exporter->temporality($this->createMock(MetricMetadataInterface::class)));
    }

    public static function temporalityProvider(): array
    {
        return [
            'default' => [
                [],
                Temporality::CUMULATIVE,
            ],
            'cumulative' => [
                [
                    'OTEL_EXPORTER_OTLP_METRICS_TEMPORALITY_PREFERENCE' => 'cumulative',
                ],
                Temporality::CUMULATIVE,
            ],
            'delta' => [
                [
                    'OTEL_EXPORTER_OTLP_METRICS_TEMPORALITY_PREFERENCE' => 'delta',
                ],
                Temporality::DELTA,
            ],
            'low memory' => [
                [
                    'OTEL_EXPORTER_OTLP_METRICS_TEMPORALITY_PREFERENCE' => 'lowmemory',
                ],
                null,
            ],
            'CuMuLaTiVe (mixed case)' => [
                [
                    'OTEL_EXPORTER_OTLP_METRICS_TEMPORALITY_PREFERENCE' => 'cumulative',
                ],
                Temporality::CUMULATIVE,
            ],
        ];
    }

    /**
     * @dataProvider configProvider
     */
    public function test_create(array $env, string $endpoint, string $protocol, string $compression, array $headerKeys = [], array $expectedValues = []): void
    {
        foreach ($env as $k => $v) {
            $this->setEnvironmentVariable($k, $v);
        }
        $factory = new MetricExporterFactory($this->transportFactory);
        // @phpstan-ignore-next-line
        $this->transportFactory
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo($endpoint),
                $this->equalTo($protocol),
                $this->callback(function ($headers) use ($headerKeys, $expectedValues) {
                    $this->assertEqualsCanonicalizing($headerKeys, array_keys($headers));
                    foreach ($expectedValues as $key => $value) {
                        $this->assertSame($value, $headers[$key]);
                    }

                    return true;
                }),
                $this->equalTo($compression)
            )
            ->willReturn($this->transport);
        // @phpstan-ignore-next-line
        $this->transport->method('contentType')->willReturn($protocol);

        $factory->create();
    }

    public static function configProvider(): array
    {
        $defaultHeaderKeys = ['User-Agent'];

        return [
            'signal-specific endpoint unchanged' => [
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_METRICS_PROTOCOL => KnownValues::VALUE_GRPC,
                    Variables::OTEL_EXPORTER_OTLP_METRICS_ENDPOINT => 'http://collector:4317/foo/bar', //should not be changed, per spec
                ],
                'endpoint' => 'http://collector:4317/foo/bar',
                'protocol' => 'application/x-protobuf',
                'compression' => 'none',
                'headerKeys' => $defaultHeaderKeys,
            ],
            'endpoint has path appended' => [
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_METRICS_PROTOCOL => KnownValues::VALUE_GRPC,
                    Variables::OTEL_EXPORTER_OTLP_ENDPOINT => 'http://collector:4317',
                ],
                'endpoint' => 'http://collector:4317/opentelemetry.proto.collector.metrics.v1.MetricsService/Export',
                'protocol' => 'application/x-protobuf',
                'compression' => 'none',
                'headerKeys' => $defaultHeaderKeys,
            ],
            'protocol' => [
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_PROTOCOL => KnownValues::VALUE_HTTP_NDJSON,
                ],
                'endpoint' => 'http://localhost:4318/v1/metrics',
                'protocol' => 'application/x-ndjson',
                'compression' => 'none',
                'headerKeys' => $defaultHeaderKeys,
            ],
            'signal-specific protocol' => [
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_PROTOCOL => KnownValues::VALUE_HTTP_JSON,
                ],
                'endpoint' => 'http://localhost:4318/v1/metrics',
                'protocol' => 'application/json',
                'compression' => 'none',
                'headerKeys' => $defaultHeaderKeys,
            ],
            'defaults' => [
                'env' => [],
                'endpoint' => 'http://localhost:4318/v1/metrics',
                'protocol' => 'application/x-protobuf',
                'compression' => 'none',
                'headerKeys' => $defaultHeaderKeys,
            ],
            'compression' => [
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_COMPRESSION => 'gzip',
                ],
                'endpoint' => 'http://localhost:4318/v1/metrics',
                'protocol' => 'application/x-protobuf',
                'compression' => 'gzip',
                'headerKeys' => $defaultHeaderKeys,
            ],
            'signal-specific compression' => [
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_METRICS_COMPRESSION => 'gzip',
                ],
                'endpoint' => 'http://localhost:4318/v1/metrics',
                'protocol' => 'application/x-protobuf',
                'compression' => 'gzip',
                'headerKeys' => $defaultHeaderKeys,
            ],
            'headers' => [
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_HEADERS => 'key1=foo,key2=bar',
                ],
                'endpoint' => 'http://localhost:4318/v1/metrics',
                'protocol' => 'application/x-protobuf',
                'compression' => 'none',
                'headerKeys' => array_merge($defaultHeaderKeys, ['key1', 'key2']),
            ],
            'signal-specific headers' => [
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_METRICS_HEADERS => 'key3=foo,key4=bar',
                ],
                'endpoint' => 'http://localhost:4318/v1/metrics',
                'protocol' => 'application/x-protobuf',
                'compression' => 'none',
                'headerKeys' => array_merge($defaultHeaderKeys, ['key3', 'key4']),
                'expectedValues' => [
                    'key3' => 'foo',
                    'key4' => 'bar',
                ],
            ],
            'url-encoded headers' => [
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_HEADERS => 'Authorization=Basic%20AAA',
                ],
                'endpoint' => 'http://localhost:4318/v1/metrics',
                'protocol' => 'application/x-protobuf',
                'compression' => 'none',
                'headerKeys' => array_merge($defaultHeaderKeys, ['Authorization']),
                'expectedValues' => [
                    'Authorization' => 'Basic AAA',
                ],
            ],
        ];
    }
}
