<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Exporter;

use InvalidArgumentException;
use OpenTelemetry\Sdk\Trace\ZipkinExporter;
use PHPUnit\Framework\TestCase;

class ZipkinExporterTest extends TestCase
{
    /**
     * @test
     */
    public function shouldParseAnValidDsn()
    {
        $exporter = new ZipkinExporter('test.zipkin', 'scheme://host:1234/path');

        $this->assertArrayHasKey('scheme', $exporter->getEndpoint());
        $this->assertArrayHasKey('host', $exporter->getEndpoint());
        $this->assertArrayHasKey('port', $exporter->getEndpoint());
        $this->assertArrayHasKey('path', $exporter->getEndpoint());
    }

    /**
     * @test
     * @dataProvider invalidDsnDataProvider
     */
    public function shouldThrowExceptionIfInvalidDsnIsPassed($invalidDsn)
    {
        $this->expectException(InvalidArgumentException::class);

        new ZipkinExporter('test.zipkin', $invalidDsn);
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
        ];
    }
}
