<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Otlp;

use OpenTelemetry\Contrib\Otlp\SpanExporterFactory;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress UndefinedInterfaceMethod
 */
#[CoversClass(SpanExporterFactory::class)]
class SpanExporterFactoryTest extends TestCase
{
    use TestState;

    private TransportFactoryInterface $transportFactory;
    private TransportInterface $transport;

    public function setUp(): void
    {
        $this->transportFactory = $this->createMock(TransportFactoryInterface::class);
        $this->transport = $this->createMock(TransportInterface::class);
    }

    public function test_unknown_protocol_exception(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->setEnvironmentVariable(Variables::OTEL_EXPORTER_OTLP_PROTOCOL, 'foo');
        $factory = new SpanExporterFactory();
        $factory->create();
    }

    #[DataProvider('configProvider')]
    public function test_create(array $env, string $endpoint, string $protocol, string $compression, array $headerKeys = [], array $expectedValues = []): void
    {
        foreach ($env as $k => $v) {
            $this->setEnvironmentVariable($k, $v);
        }
        $factory = new SpanExporterFactory($this->transportFactory);
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
                        $this->assertSame($headers[$key], $value);
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
                    Variables::OTEL_EXPORTER_OTLP_TRACES_PROTOCOL => KnownValues::VALUE_GRPC,
                    Variables::OTEL_EXPORTER_OTLP_TRACES_ENDPOINT => 'http://collector:4317/foo/bar', //should not be changed, per spec
                ],
                'endpoint' => 'http://collector:4317/foo/bar',
                'protocol' => 'application/x-protobuf',
                'compression' => 'none',
                'headerKeys' => $defaultHeaderKeys,
            ],
            'endpoint has path appended' => [
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_TRACES_PROTOCOL => KnownValues::VALUE_GRPC,
                    Variables::OTEL_EXPORTER_OTLP_ENDPOINT => 'http://collector:4317',
                ],
                'endpoint' => 'http://collector:4317/opentelemetry.proto.collector.trace.v1.TraceService/Export',
                'protocol' => 'application/x-protobuf',
                'compression' => 'none',
                'headerKeys' => $defaultHeaderKeys,
            ],
            'protocol' => [
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_PROTOCOL => KnownValues::VALUE_HTTP_NDJSON,
                ],
                'endpoint' => 'http://localhost:4318/v1/traces',
                'protocol' => 'application/x-ndjson',
                'compression' => 'none',
                'headerKeys' => $defaultHeaderKeys,
            ],
            'signal-specific protocol' => [
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_PROTOCOL => KnownValues::VALUE_HTTP_JSON,
                ],
                'endpoint' => 'http://localhost:4318/v1/traces',
                'protocol' => 'application/json',
                'compression' => 'none',
                'headerKeys' => $defaultHeaderKeys,
            ],
            'defaults' => [
                'env' => [],
                'endpoint' => 'http://localhost:4318/v1/traces',
                'protocol' => 'application/x-protobuf',
                'compression' => 'none',
                'headerKeys' => $defaultHeaderKeys,
            ],
            'compression' => [
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_COMPRESSION => 'gzip',
                ],
                'endpoint' => 'http://localhost:4318/v1/traces',
                'protocol' => 'application/x-protobuf',
                'compression' => 'gzip',
                'headerKeys' => $defaultHeaderKeys,
            ],
            'signal-specific compression' => [
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_TRACES_COMPRESSION => 'gzip',
                ],
                'endpoint' => 'http://localhost:4318/v1/traces',
                'protocol' => 'application/x-protobuf',
                'compression' => 'gzip',
                'headerKeys' => $defaultHeaderKeys,
            ],
            'headers' => [
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_HEADERS => 'key1=foo,key2=bar',
                ],
                'endpoint' => 'http://localhost:4318/v1/traces',
                'protocol' => 'application/x-protobuf',
                'compression' => 'none',
                'headerKeys' => array_merge($defaultHeaderKeys, ['key1', 'key2']),
            ],
            'signal-specific headers' => [
                'env' => [
                    Variables::OTEL_EXPORTER_OTLP_TRACES_HEADERS => 'key3=foo,key4=bar',
                ],
                'endpoint' => 'http://localhost:4318/v1/traces',
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
                'endpoint' => 'http://localhost:4318/v1/traces',
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
