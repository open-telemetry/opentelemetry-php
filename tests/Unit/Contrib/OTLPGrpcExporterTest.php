<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Grpc\UnaryCall;
use Mockery;
use Mockery\MockInterface;
use OpenTelemetry\Contrib\OtlpGrpc\Exporter;
use Opentelemetry\Proto\Collector\Trace\V1\TraceServiceClient;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\Tests\Unit\SDK\Trace\SpanExporter\AbstractExporterTest;
use OpenTelemetry\Tests\Unit\SDK\Util\SpanData;
use org\bovigo\vfs\vfsStream;

/**
 * @covers OpenTelemetry\Contrib\OtlpGrpc\Exporter
 */
class OTLPGrpcExporterTest extends AbstractExporterTest
{
    use EnvironmentVariables;

    public function createExporter(): SpanExporterInterface
    {
        return new Exporter();
    }

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    /**
     * @psalm-suppress UndefinedConstant
     */
    public function test_exporter_happy_path(): void
    {
        $exporter = new Exporter(
            //These first parameters were copied from the constructor's default values
            'localhost:4317',
            true,
            '',
            '',
            false,
            10,
            $this->createMockTraceServiceClient([
                'expectations' => [
                    'num_spans' => 1,
                ],
                'return_values' => [
                    'status_code' => \Grpc\STATUS_OK,
                ],
            ])
        );

        $exporterStatusCode = $exporter->export([new SpanData()]);

        $this->assertSame(SpanExporterInterface::STATUS_SUCCESS, $exporterStatusCode);
    }

    public function test_exporter_unexpected_grpc_response_status(): void
    {
        $exporter = new Exporter(
            //These first parameters were copied from the constructor's default values
            'localhost:4317',
            true,
            '',
            '',
            false,
            10,
            $this->createMockTraceServiceClient([
                'expectations' => [
                    'num_spans' => 1,
                ],
                'return_values' => [
                    'status_code' => 'An unexpected status',
                ],
            ])
        );

        $exporterStatusCode = $exporter->export([new SpanData()]);

        $this->assertSame(SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE, $exporterStatusCode);
    }

    public function test_exporter_grpc_responds_as_unavailable(): void
    {
        $this->assertEquals(SpanExporterInterface::STATUS_FAILED_RETRYABLE, (new Exporter())->export([new SpanData()]));
    }

    public function test_set_headers_with_environment_variables(): void
    {
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_HEADERS', 'x-aaa=foo,x-bbb=barf');

        $exporter = new Exporter();

        $this->assertEquals(['x-aaa' => 'foo', 'x-bbb' => 'barf'], $exporter->getHeaders());
    }

    public function test_set_header(): void
    {
        $exporter = new Exporter();
        $exporter->setHeader('foo', 'bar');
        $headers = $exporter->getHeaders();
        $this->assertArrayHasKey('foo', $headers);
        $this->assertEquals('bar', $headers['foo']);
    }

    public function test_set_headers_in_constructor(): void
    {
        $exporter = new Exporter('localhost:4317', true, '', 'x-aaa=foo,x-bbb=bar');

        $this->assertEquals(['x-aaa' => 'foo', 'x-bbb' => 'bar'], $exporter->getHeaders());

        $exporter->setHeader('key', 'value');

        $this->assertEquals(['x-aaa' => 'foo', 'x-bbb' => 'bar', 'key' => 'value'], $exporter->getHeaders());
    }

    public function test_should_be_ok_to_exporter_empty_spans_collection(): void
    {
        $this->assertEquals(
            SpanExporterInterface::STATUS_SUCCESS,
            (new Exporter('test.otlp'))->export([])
        );
    }

    private function isInsecure(Exporter $exporter) : bool
    {
        $reflection = new \ReflectionClass($exporter);
        $property = $reflection->getProperty('insecure');
        $property->setAccessible(true);

        return $property->getValue($exporter);
    }

    public function test_client_options(): void
    {
        // default options
        $exporter = new Exporter('localhost:4317');
        $opts = $exporter->getClientOptions();
        $this->assertEquals(10, $opts['timeout']);
        $this->assertTrue($this->isInsecure($exporter));
        $this->assertArrayNotHasKey('grpc.default_compression_algorithm', $opts);
        // method args
        $exporter = new Exporter('localhost:4317', false, '', '', true, 5);
        $opts = $exporter->getClientOptions();
        $this->assertEquals(5, $opts['timeout']);
        $this->assertFalse($this->isInsecure($exporter));
        $this->assertEquals(2, $opts['grpc.default_compression_algorithm']);
        // env vars
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_TIMEOUT', '1');
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_COMPRESSION', 'gzip');
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_INSECURE', 'false');
        $exporter = new Exporter('localhost:4317');
        $opts = $exporter->getClientOptions();
        $this->assertEquals(1, $opts['timeout']);
        $this->assertFalse($this->isInsecure($exporter));
        $this->assertEquals(2, $opts['grpc.default_compression_algorithm']);
    }

    /**
     * @psalm-suppress PossiblyUndefinedMethod
     * @psalm-suppress UndefinedMagicMethod
     */
    private function createMockTraceServiceClient(array $options = [])
    {
        [
            'expectations' => [
                'num_spans' => $expectedNumSpans,
            ],
            'return_values' => [
                'status_code' => $statusCode,
            ]
        ] = $options;

        /** @var MockInterface&TraceServiceClient */
        $mockClient = Mockery::mock(TraceServiceClient::class)
                        ->allows('Export')
                        ->withArgs(function ($request) use ($expectedNumSpans) {
                            return (count($request->getResourceSpans()) === $expectedNumSpans);
                        })
                        ->andReturns(
                            Mockery::mock(UnaryCall::class)
                                ->allows('wait')
                                ->andReturns(
                                    [
                                        'unused response data',
                                        new class($statusCode) {
                                            public $code;

                                            public function __construct($code)
                                            {
                                                $this->code = $code;
                                            }
                                        },
                                    ]
                                )
                                ->getMock()
                        )
                        ->getMock();

        return $mockClient;
    }

    public function test_from_connection_string(): void
    {
        // @phpstan-ignore-next-line
        $this->assertNotSame(
            Exporter::fromConnectionString(),
            Exporter::fromConnectionString()
        );
    }

    public function test_create_with_cert_file(): void
    {
        $certDir = 'var';
        $certFile = 'file.cert';
        vfsStream::setup($certDir);
        $certPath = vfsStream::url(sprintf('%s/%s', $certDir, $certFile));
        file_put_contents($certPath, 'foo');

        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_INSECURE', 'false');
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_CERTIFICATE', $certPath);

        $this->assertSame(
            $certPath,
            (new Exporter())->getCertificateFile()
        );
    }
}
