<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use Grpc\UnaryCall;
use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use OpenTelemetry\Contrib\OtlpGrpc\Exporter;
use Opentelemetry\Proto\Collector\Trace\V1\TraceServiceClient;
use OpenTelemetry\Tests\SDK\Util\SpanData;

class OTLPGrpcExporterTest extends MockeryTestCase
{
    public function testExporterHappyPath()
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

        $this->assertSame(Exporter::STATUS_SUCCESS, $exporterStatusCode);
    }

    public function testExporterUnexpectedGrpcResponseStatus()
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

        $this->assertSame(Exporter::STATUS_FAILED_NOT_RETRYABLE, $exporterStatusCode);
    }

    public function testExporterGrpcRespondsAsUnavailable()
    {
        $this->assertEquals(Exporter::STATUS_FAILED_RETRYABLE, (new Exporter())->export([new SpanData()]));
    }

    public function testRefusesInvalidHeaders()
    {
        $foo = new Exporter('localhost:4317', true, '', 'a:bc');

        $this->assertEquals([], $foo->getHeaders());

        //$this->expectException(InvalidArgumentException::class);
    }

    public function testSetHeadersWithEnvironmentVariables()
    {
        putenv('OTEL_EXPORTER_OTLP_HEADERS=x-aaa=foo,x-bbb=barf');

        $exporter = new Exporter();

        $this->assertEquals(['x-aaa' => ['foo'], 'x-bbb' => ['barf']], $exporter->getHeaders());

        putenv('OTEL_EXPORTER_OTLP_HEADERS'); // Clear the envvar or it breaks future tests
    }

    public function testSetHeadersInConstructor()
    {
        $exporter = new Exporter('localhost:4317', true, '', 'x-aaa=foo,x-bbb=bar');

        $this->assertEquals(['x-aaa' => ['foo'], 'x-bbb' => ['bar']], $exporter->getHeaders());

        $exporter->setHeader('key', 'value');

        $this->assertEquals(['x-aaa' => ['foo'], 'x-bbb' => ['bar'], 'key' => ['value']], $exporter->getHeaders());
    }

    /**
     * @test
     */
    public function shouldBeOkToExporterEmptySpansCollection()
    {
        $this->assertEquals(
            Exporter::STATUS_SUCCESS,
            (new Exporter('test.otlp'))->export([])
        );
    }
    /**
     * @test
     */
    public function failsIfNotRunning()
    {
        $exporter = new Exporter('test.otlp');
        $span = $this->createMock(SpanData::class);
        $exporter->shutdown();

        $this->assertSame(Exporter::STATUS_FAILED_NOT_RETRYABLE, $exporter->export([$span]));
    }

    public function testHeadersShouldRefuseArray()
    {
        $headers = [
            'key' => ['value'],
        ];

        $this->expectException(InvalidArgumentException::class);

        $headers_as_string = (new Exporter())->metadataFromHeaders($headers);
    }

    public function testMetadataFromHeaders()
    {
        $metadata = (new Exporter())->metadataFromHeaders('key=value');
        $this->assertEquals(['key' => ['value']], $metadata);

        $metadata = (new Exporter())->metadataFromHeaders('key=value,key2=value2');
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
        $exporter = new Exporter('localhost:4317');
        $opts = $exporter->getClientOptions();
        $this->assertEquals(10, $opts['timeout']);
        $this->assertTrue($this->isInsecure($exporter));
        $this->assertFalse(array_key_exists('grpc.default_compression_algorithm', $opts));
        // method args
        $exporter = new Exporter('localhost:4317', false, '', '', true, 5);
        $opts = $exporter->getClientOptions();
        $this->assertEquals(5, $opts['timeout']);
        $this->assertFalse($this->isInsecure($exporter));
        $this->assertEquals(2, $opts['grpc.default_compression_algorithm']);
        // env vars
        putenv('OTEL_EXPORTER_OTLP_TIMEOUT=1');
        putenv('OTEL_EXPORTER_OTLP_COMPRESSION=1');
        putenv('OTEL_EXPORTER_OTLP_INSECURE=false');
        $exporter = new Exporter('localhost:4317');
        $opts = $exporter->getClientOptions();
        $this->assertEquals(1, $opts['timeout']);
        $this->assertFalse($this->isInsecure($exporter));
        $this->assertEquals(2, $opts['grpc.default_compression_algorithm']);
        putenv('OTEL_EXPORTER_OTLP_TIMEOUT');
        putenv('OTEL_EXPORTER_OTLP_COMPRESSION');
        putenv('OTEL_EXPORTER_OTLP_INSECURE');
    }

    public function test_shutdown(): void
    {
        $exporter = new Exporter('localhost:4317');

        $this->assertTrue($exporter->shutdown());
        $this->assertFalse($exporter->shutdown());
    }

    public function test_forceFlush(): void
    {
        $this->assertTrue((new Exporter('localhost:4317'))->forceFlush());
    }

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
                        ->shouldReceive('Export')
                        ->withArgs(function ($request) use ($expectedNumSpans) {
                            return (count($request->getResourceSpans()) == $expectedNumSpans);
                        })
                        ->andReturn(
                            Mockery::mock(UnaryCall::class)
                                ->shouldReceive('wait')
                                ->andReturn(
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
