<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use Grpc\UnaryCall;
use Mockery;
use Mockery\MockInterface;
use OpenTelemetry\Contrib\Otlp\ConfigOpts;
use OpenTelemetry\Contrib\OtlpGrpc\Exporter;
use Opentelemetry\Proto\Collector\Trace\V1\TraceServiceClient;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\Tests\SDK\Unit\Trace\SpanExporter\AbstractExporterTest;
use OpenTelemetry\Tests\SDK\Util\SpanData;

class OTLPGrpcExporterTest extends AbstractExporterTest
{
    use EnvironmentVariables;

    private ConfigOpts $config;

    public function createExporter(): SpanExporterInterface
    {
        return new Exporter($this->config->withProtocol('grpc'));
    }

    public function setUp(): void
    {
        $this->config = (new ConfigOpts())->withEndpoint('localhost:4317')->withInsecure()->withProtocol('grpc');
    }

    public function tearDown(): void
    {
        $this->restoreEnvironmentVariables();
    }

    /**
     * @psalm-suppress UndefinedConstant
     */
    public function testExporterHappyPath(): void
    {
        $exporter = new Exporter(
            $this->config
                ->withProtocol('grpc')
                ->withGrpcTraceServiceClient(
                    $this->createMockTraceServiceClient([
                        'expectations' => [
                            'num_spans' => 1,
                        ],
                        'return_values' => [
                            'status_code' => \Grpc\STATUS_OK,
                        ],
                    ])
                )
        );

        $exporterStatusCode = $exporter->export([new SpanData()]);

        $this->assertSame(SpanExporterInterface::STATUS_SUCCESS, $exporterStatusCode);
    }

    public function testExporterUnexpectedGrpcResponseStatus(): void
    {
        $exporter = new Exporter(
            $this->config->withGrpcTraceServiceClient(
                $this->createMockTraceServiceClient([
                    'expectations' => [
                        'num_spans' => 1,
                    ],
                    'return_values' => [
                        'status_code' => 'An unexpected status',
                    ],
                ])
            )
        );

        $exporterStatusCode = $exporter->export([new SpanData()]);

        $this->assertSame(SpanExporterInterface::STATUS_FAILED_NOT_RETRYABLE, $exporterStatusCode);
    }

    public function testExporterGrpcRespondsAsUnavailable(): void
    {
        $this->assertEquals(SpanExporterInterface::STATUS_FAILED_RETRYABLE, (new Exporter($this->config))->export([new SpanData()]));
    }

    /**
     * @test
     */
    public function shouldBeOkToExporterEmptySpansCollection(): void
    {
        $this->assertEquals(
            SpanExporterInterface::STATUS_SUCCESS,
            (new Exporter($this->config))->export([])
        );
    }

    public function testMetadata(): void
    {
        $metadata = (new Exporter($this->config->withHeaders('key=value')))->getMetadata();
        $this->assertEquals(['key' => ['value']], $metadata);

        $metadata = (new Exporter($this->config->withHeaders('key=value,key2=value2')))->getMetadata();
        $this->assertEquals(['key' => ['value'], 'key2' => ['value2']], $metadata);
    }

    private function isInsecure(Exporter $exporter) : bool
    {
        $reflection = new \ReflectionClass($exporter);
        $property = $reflection->getProperty('insecure');
        $property->setAccessible(true);

        return $property->getValue($exporter);
    }

    public function testClientOptions()
    {
        // default options
        $exporter = new Exporter($this->config);
        $opts = $exporter->getClientOptions();
        $this->assertEquals(10, $opts['timeout']);
        $this->assertTrue($this->isInsecure($exporter));
        $this->assertArrayNotHasKey('grpc.default_compression_algorithm', $opts);

        // method args
        $exporter = new Exporter($this->config->withSecure()->withCompression()->withTimeout(5));
        //$exporter = new Exporter('localhost:4317', false, '', '', true, 5);
        $opts = $exporter->getClientOptions();
        $this->assertEquals(5, $opts['timeout']);
        $this->assertFalse($this->isInsecure($exporter));
        $this->assertEquals(2, $opts['grpc.default_compression_algorithm']);

        // env vars
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_TIMEOUT', '1');
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_COMPRESSION', 'gzip');
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_INSECURE', 'false');
        $exporter = new Exporter((new ConfigOpts())->withEndPoint('localhost:4317')->withProtocol('grpc'));
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
}
