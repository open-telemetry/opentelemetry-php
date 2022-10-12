<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Benchmark;

use Mockery;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransport;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
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
        $this->resource = ResourceInfo::create(Attributes::create([
            'service.name' => 'A123456789',
            'service.version' => '1.34567890',
            'service.instance.id' => '123ab456-a123-12ab-12ab-12340a1abc12',
        ]));
    }

    public function setUpNoExporter(): void
    {
        $processor = new NoopSpanProcessor();
        $provider = new TracerProvider($processor, $this->sampler, $this->resource);
        $this->tracer = $provider->getTracer('io.opentelemetry.contrib.php');
    }

    /**
     * @psalm-suppress InvalidArgument
     */
    public function setUpGrpc(): void
    {
        $transport = Mockery::mock(TransportInterface::class)->allows([
            'send' => new CompletedFuture('ok'),
            'shutdown' => true,
            'forceFlush' => true,
            'contentType' => 'application/x-protobuf',
        ]);
        $exporter = new SpanExporter($transport);
        $processor = new SimpleSpanProcessor($exporter);
        $provider = new TracerProvider($processor, $this->sampler, $this->resource);
        $this->tracer = $provider->getTracer('io.opentelemetry.contrib.php');
    }

    /**
     * @psalm-suppress UndefinedMagicMethod
     * @psalm-suppress InvalidArgument
     * @psalm-suppress PossiblyUndefinedMethod
     */
    public function setUpOtlpHttp(): void
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
        $transport = new PsrTransport($client, $requestFactory, $streamFactory, 'http://foo', 'application/x-protobuf', [], [], 0, 0); // @phpstan-ignore-line
        $exporter = new SpanExporter($transport);

        $processor = new SimpleSpanProcessor($exporter);
        $provider = new TracerProvider($processor, $this->sampler, $this->resource);
        $this->tracer = $provider->getTracer('io.opentelemetry.contrib.php');
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
     * @BeforeMethods("setUpOtlpHttp")
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
}
