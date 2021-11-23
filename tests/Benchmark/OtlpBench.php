<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Benchmark;

use Grpc\UnaryCall;
use Mockery;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\Attributes;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\Contrib\OtlpGrpc\Exporter as GrpcExporter;
use OpenTelemetry\Contrib\OtlpHttp\Exporter as HttpExporter;
use Opentelemetry\Proto\Collector\Trace\V1\TraceServiceClient;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * TODO https://github.com/open-telemetry/opentelemetry-go/blob/051227c9edded2c772a07a4d4e60dda27c3e4b20/sdk/trace/benchmark_test.go
 */
class OtlpBench
{
    private ?TracerInterface $tracer;

    public function __construct()
    {
        $this->sampler = new AlwaysOnSampler();
        $this->resource = ResourceInfo::create(new Attributes([
            'service.name' => 'A123456789',
            'service.version' => '1.34567890',
            'service.instance.id' => '123ab456-a123-12ab-12ab-12340a1abc12',
        ]));
    }

    public function setUpNoExporter(): void
    {
        $provider = new TracerProvider([], $this->sampler, $this->resource);
        $this->tracer = $provider->getTracer();
    }

    public function setUpGrpc(): void
    {
        $client = $this->createMockTraceServiceClient();
        $exporter = new GrpcExporter('foo:4317', true, '', '', false, 10, $client);
        $processor = new SimpleSpanProcessor($exporter);
        $provider = new TracerProvider($processor, $this->sampler, $this->resource);
        $this->tracer = $provider->getTracer();
    }

    public function setUpGrpcHttp(): void
    {
        $response = Mockery::mock(ResponseInterface::class)
                ->allows('getStatusCode')
                ->andReturn(200)
                ->getMock();
        $stream = Mockery::mock(StreamInterface::class);
        $request = Mockery::mock(RequestInterface::class)
                ->allows('withBody')
                ->andReturnSelf()
                ->getMock();
        $request->allows('withHeader')->andReturnSelf();
        $client = Mockery::mock(ClientInterface::class)
                ->allows('sendRequest')
                ->andReturns($response)
                ->getMock();
        $requestFactory = Mockery::mock(RequestFactoryInterface::class)
                ->allows('createRequest')
                ->andReturns($request)
                ->getMock();
        $streamFactory = Mockery::mock(StreamFactoryInterface::class)
                ->allows('createStream')
                ->andReturns($stream)
                ->getMock();
        $exporter = new HttpExporter($client, $requestFactory, $streamFactory);
        $processor = new SimpleSpanProcessor($exporter);
        $provider = new TracerProvider($processor, $this->sampler, $this->resource);
        $this->tracer = $provider->getTracer();
    }

    /**
     * @BeforeMethods("setUpNoExporter")
     * @Revs(1000)
     * @Iterations(10)
     * @OutputTimeUnit("microseconds")
     */
    public function benchCreateSpansWithoutExporting()
    {
        $span = $this->tracer->spanBuilder('foo')
            ->setAttribute('foo', PHP_INT_MAX)
            ->startSpan();
        $span->addEvent('my_event');
        $span->end();
    }

    /**
     * @BeforeMethods("setUpGrpc")
     * @Revs(1000)
     * @Iterations(10)
     * @OutputTimeUnit("microseconds")
     */
    public function benchExportSpansViaOltpGrpc()
    {
        $span = $this->tracer->spanBuilder('foo')
            ->setAttribute('foo', PHP_INT_MAX)
            ->startSpan();
        $span->addEvent('my_event');
        $span->end();
    }

    /**
     * @BeforeMethods("setUpGrpcHttp")
     * @Revs(1000)
     * @Iterations(10)
     * @OutputTimeUnit("microseconds")
     */
    public function benchExportSpansViaOtlpHttp()
    {
        $span = $this->tracer->spanBuilder('foo')
            ->setAttribute('foo', PHP_INT_MAX)
            ->startSpan();
        $span->addEvent('my_event');
        $span->end();
    }

    private function createMockTraceServiceClient(): TraceServiceClient
    {
        $mockClient = Mockery::mock(TraceServiceClient::class)
            ->allows('Export')
            ->andReturns(
                Mockery::mock(UnaryCall::class)
                    ->allows('wait')
                    ->andReturns(
                        [
                            'unused response data',
                            (object)[
                                'code' => \Grpc\STATUS_OK,
                            ],
                        ]
                    )
                    ->getMock()
            )
            ->getMock();

        return $mockClient;
    }
}