<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Benchmark;

use Grpc\UnaryCall;
use Mockery;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Contrib\OtlpGrpc\Exporter as GrpcExporter;
use OpenTelemetry\Contrib\OtlpHttp\Exporter as HttpExporter;
use Opentelemetry\Proto\Collector\Trace\V1\TraceServiceClient;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\Attributes;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
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
    private TracerInterface $tracer;
    private SamplerInterface $sampler;
    private ResourceInfo $resource;

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
        $processor = new SimpleSpanProcessor();
        $provider = new TracerProvider($processor, $this->sampler, $this->resource);
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

    /**
     * @psalm-suppress UndefinedMagicMethod
     * @psalm-suppress InvalidArgument
     * @psalm-suppress PossiblyUndefinedMethod
     */
    public function setUpGrpcHttp(): void
    {
        $response = Mockery::mock(ResponseInterface::class)
            ->allows(['getStatusCode' => 200]);
        $stream = Mockery::mock(StreamInterface::class);
        $request = Mockery::mock(RequestInterface::class);
        $request->allows('withBody')->andReturnSelf();
        $request->allows('withHeader')->andReturnSelf();
        $client = Mockery::mock(ClientInterface::class)
            ->allows(['sendRequest' => $response]);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class)
            ->allows(['createRequest' => $request]);
        $streamFactory = Mockery::mock(StreamFactoryInterface::class)
            ->allows(['createStream' => $stream]);
        // @phpstan-ignore-next-line
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
    public function benchCreateSpans(): void
    {
        $span = $this->tracer->spanBuilder('foo')
            ->setAttribute('foo', PHP_INT_MAX)
            ->startSpan();
        $span->addEvent('my_event');
        $span->end();
    }

    /**
     * @BeforeMethods("setUpNoExporter")
     * @Revs(1000)
     * @Iterations(10)
     * @OutputTimeUnit("microseconds")
     */
    public function benchCreateSpansWithStackTrace(): void
    {
        $span = $this->tracer->spanBuilder('foo')
            ->setAttribute('foo', PHP_INT_MAX)
            ->startSpan();
        $span->recordException(new \Exception('foo'));
        $span->end();
    }

    /**
     * @BeforeMethods("setUpNoExporter")
     * @ParamProviders("provideEventCounts")
     * @Revs(1000)
     * @Iterations(5)
     * @OutputTimeUnit("microseconds")
     */
    public function benchCreateSpansWithMultipleEvents(array $params): void
    {
        $span = $this->tracer->spanBuilder('foo')
            ->setAttribute('foo', PHP_INT_MAX)
            ->startSpan();
        for ($i=0; $i < $params[0]; $i++) {
            $span->addEvent('event-' . $i);
        }
        $span->end();
    }

    public function provideEventCounts(): \Generator
    {
        yield [1];
        yield [4];
        yield [16];
        yield [256];
    }

    /**
     * @BeforeMethods("setUpGrpc")
     * @Revs(1000)
     * @Iterations(10)
     * @OutputTimeUnit("microseconds")
     */
    public function benchExportSpans_OltpGrpc(): void
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
    public function benchExportSpans_OtlpHttp(): void
    {
        $span = $this->tracer->spanBuilder('foo')
            ->setAttribute('foo', PHP_INT_MAX)
            ->startSpan();
        $span->addEvent('my_event');
        $span->end();
    }

    private function createMockTraceServiceClient()
    {
        // @var MockInterface&TraceServiceClient
        $unaryCall = Mockery::mock(UnaryCall::class)
            ->allows(['wait' => [
                'unused response data',
                (object) ['code' => \Grpc\STATUS_OK],
            ]]);
        $mockClient = Mockery::mock(TraceServiceClient::class)
            ->allows(['Export'=> $unaryCall]);

        return $mockClient;
    }
}
