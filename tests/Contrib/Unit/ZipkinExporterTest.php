<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use InvalidArgumentException;
use OpenTelemetry\Contrib\Zipkin\Exporter;
use PHPUnit\Framework\TestCase;

class ZipkinExporterTest extends TestCase
{
    /**
     * @test
     * @dataProvider invalidDsnDataProvider
     */
    public function shouldThrowExceptionIfInvalidDsnIsPassed($invalidDsn)
    {
        $this->expectException(InvalidArgumentException::class);

        new Exporter('test.zipkin', $invalidDsn);
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
