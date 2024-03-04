<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Benchmark;

use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SamplerInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\NoopSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

/**
 * TODO https://github.com/open-telemetry/opentelemetry-go/blob/051227c9edded2c772a07a4d4e60dda27c3e4b20/sdk/trace/benchmark_test.go
 */
class OtlpBench
{
    private TracerInterface $tracer;
    private SamplerInterface $sampler;
    private ResourceInfo $resource;

    private const PROTOBUF = 'application/x-protobuf';
    private const JSON = 'application/json';
    private const SIMPLE = 'simple';
    private const BATCH = 'batch';

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
     * @psalm-suppress MissingTemplateParam
     */
    private function createTransport(string $contentType): TransportInterface
    {
        return new class($contentType) implements TransportInterface {
            private string $contentType;

            public function __construct(string $contentType)
            {
                $this->contentType = $contentType;
            }

            public function contentType(): string
            {
                return $this->contentType;
            }

            public function send(string $payload, ?CancellationInterface $cancellation = null): FutureInterface
            {
                return new CompletedFuture('');
            }

            public function shutdown(?CancellationInterface $cancellation = null): bool
            {
                return true;
            }

            public function forceFlush(?CancellationInterface $cancellation = null): bool
            {
                return true;
            }
        };
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function setUpOtlpExporter(array $params): void
    {
        $transport = $this->createTransport($params[0]);
        $exporter = new SpanExporter($transport);
        $processor = $params[1] === self::SIMPLE
            ? new SimpleSpanProcessor($exporter)
            : new BatchSpanProcessor($exporter, ClockFactory::getDefault());
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
        yield 'no events' => [0];
        yield '1 event' => [1];
        yield '4 events' => [4];
        yield '16 events' => [16];
        yield '256 events' => [256];
    }

    /**
     * @BeforeMethods("setUpOtlpExporter")
     * @ParamProviders("provideOtlp")
     * @Revs({100, 1000})
     * @Iterations(10)
     * @OutputTimeUnit("microseconds")
     */
    public function benchExportSpans_Oltp(): void
    {
        $span = $this->tracer->spanBuilder('foo')
            ->setAttribute('foo', PHP_INT_MAX)
            ->startSpan();
        $span->addEvent('my_event');
        $span->end();
    }

    public function provideOtlp(): \Generator
    {
        yield 'protobuf+simple' => [self::PROTOBUF, self::SIMPLE];
        yield 'protobuf+batch' => [self::PROTOBUF, self::BATCH];
        yield 'json+simple' => [self::JSON, self::SIMPLE];
        yield 'json+batch' => [self::JSON, self::BATCH];
    }
}
