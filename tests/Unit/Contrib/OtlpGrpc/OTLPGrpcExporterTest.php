<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\OtlpGrpc;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Grpc\UnaryCall;
use Mockery;
use Mockery\MockInterface;
use OpenTelemetry\Contrib\OtlpGrpc\Exporter;
use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceResponse;
use Opentelemetry\Proto\Collector\Trace\V1\TraceServiceClient;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\Tests\Unit\SDK\Trace\SpanExporter\AbstractExporterTest;
use OpenTelemetry\Tests\Unit\SDK\Util\SpanData;
use org\bovigo\vfs\vfsStream;

/**
 * @covers \OpenTelemetry\Contrib\OtlpGrpc\Exporter
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
                'partial_success' => [
                    'has_partial_success' => false,
                    'rejected' => 0,
                    'error' => '',
                ],
            ])
        );

        $exporterStatusCode = $exporter->export([new SpanData()])->await();

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
                'partial_success' => [
                    'has_partial_success' => false,
                    'rejected' => 0,
                    'error' => '',
                ],
            ])
        );

        $exporterStatusCode = $exporter->export([new SpanData()])->await();

        $this->assertSame(SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE, $exporterStatusCode);
    }

    public function test_exporter_grpc_responds_as_unavailable(): void
    {
        $this->assertEquals(SpanExporterInterface::STATUS_FAILED_RETRYABLE, (new Exporter())->export([new SpanData()])->await());
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
     * @dataProvider partialSuccessProvider
     */
    public function test_partial_success(int $rejected, string $error, int $expectedStatusCode): void
    {
        $exporter = new Exporter(
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
                    'status_code' => null,
                ],
                'partial_success' => [
                    'has_partial_success' => true,
                    'rejected' => $rejected,
                    'error' => $error,
                ],
            ])
        );

        $exporterStatusCode = $exporter->export([new SpanData()])->await();

        $this->assertSame($expectedStatusCode, $exporterStatusCode);
    }

    public function partialSuccessProvider(): array
    {
        return [
            'partial success with dropped' => [1, 'some.error.message', SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE],
            'partial success with no dropped and warning' => [0, 'some.warning.message', SpanExporterInterface::STATUS_SUCCESS],
            'partial success with full success' => [0, '', SpanExporterInterface::STATUS_SUCCESS],
        ];
    }

    /**
     * Mocking protobuf classes with C extension explicitly not supported
     * @see https://github.com/protocolbuffers/protobuf/issues/4107#issuecomment-509358356
     */
    private function createMockExportTracePartialSuccess(int $rejectedNumSpans = 0, string $error = '')
    {
        return new class($rejectedNumSpans, $error) {
            private int $rejectedNumSpans;
            private string $error;
            public function __construct(int $rejectedNumSpans, string $error)
            {
                $this->rejectedNumSpans = $rejectedNumSpans;
                $this->error = $error;
            }
            public function getRejectedSpans(): int
            {
                return $this->rejectedNumSpans;
            }
            public function getErrorMessage(): string
            {
                return $this->error;
            }
        };
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
            ],
            'partial_success' => [
                'has_partial_success' => $hasPartialSuccess,
                'rejected' => $rejectedNumSpans,
                'error' => $partialErrorMessage,
            ],
        ] = $options;
        $partial = $hasPartialSuccess
            ? $this->createMockExportTracePartialSuccess($rejectedNumSpans, $partialErrorMessage)
            : null;

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
                                        // @note mocking ExportTraceServiceResponse with protobuf extension = segfault
                                        new class($partial) {
                                            private $partial;
                                            public function __construct($partial)
                                            {
                                                $this->partial = $partial;
                                            }
                                            public function hasPartialSuccess(): bool
                                            {
                                                return $this->partial !== null;
                                            }
                                            public function getPartialSuccess()
                                            {
                                                return $this->partial;
                                            }
                                        },
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
