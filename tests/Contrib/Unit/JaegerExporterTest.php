<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use InvalidArgumentException;
use OpenTelemetry\Contrib\Jaeger\Exporter;
use OpenTelemetry\Tests\SDK\Util\SpanData;
use PHPUnit\Framework\TestCase;

class JaegerExporterTest extends TestCase
{
    public function test_shutdown(): void
    {
        $exporter = new Exporter('test.jaeger', 'https://localhost:1234/foo', new Client(), new HttpFactory(), new HttpFactory());

        $this->assertTrue($exporter->shutdown());
        $this->assertFalse($exporter->shutdown());
    }

    public function test_forceFlush(): void
    {
        $this->assertTrue((new Exporter('test.jaeger', 'https://localhost:1234/foo', new Client(), new HttpFactory(), new HttpFactory()))->forceFlush());
    }

    /**
     * @test
     * @dataProvider invalidDsnDataProvider
     */
    public function shouldThrowExceptionIfInvalidDsnIsPassed($invalidDsn)
    {
        $this->expectException(InvalidArgumentException::class);

        new Exporter('test.jaeger', $invalidDsn, new Client(), new HttpFactory(), new HttpFactory());
    }

    public function invalidDsnDataProvider()
    {
        return [
            'missing scheme' => ['host:123/path'],
            'missing host' => ['scheme://123/path'],
            'missing port' => ['scheme://host/path'],
            'missing path' => ['scheme://host:123'],
            'invalid port' => ['scheme://host:port/path'],
            'invalid scheme' => ['1234://host:port/path'],
            'invalid host' => ['scheme:///end:1234/path'],
            'unimplemented path' => ['scheme:///host:1234/api/v1/spans'],
        ];
    }

    /**
     * @test
     */
    public function failsIfNotRunning()
    {
        $exporter = new Exporter(
            'test.jaeger',
            'scheme://host:123/api/v1/spans',
            new Client(),
            new HttpFactory(),
            new HttpFactory()
        );

        $span = $this->createMock(SpanData::class);
        $exporter->shutdown();

        $this->assertSame(Exporter::STATUS_FAILED_NOT_RETRYABLE, $exporter->export([$span]));
    }
}
