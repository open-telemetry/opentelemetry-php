<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use InvalidArgumentException;
use OpenTelemetry\Contrib\Otlp\ConfigOpts;


use PHPUnit\Framework\TestCase;

class OTLPConfigOptsTest extends TestCase
{

    public function testHappyConfigOps()
    {
        $otlpConfig = new ConfigOpts([]);

        $otlpConfig->WithEndpoint('https://api.example.com:1337/v1/trace')
                   ->WithHeaders('X-Auth-Wibble=foo,X-Dataset=bar')
                   ->WithProtocol('http/protobuf')
                   ->WithInsecure()
                   ->WithCompression()
                   ->WithTimeout(10);


        $this->assertSame([], $otlpConfig);

    }



    // public function testRefusesInvalidHeaders()
    // {
    //     $foo = new ConfigOpts();

    //     $this->assertEquals([], $foo->getHeaders());

    //     //$this->expectException(InvalidArgumentException::class);
    // }

    // public function testSetHeadersWithEnvironmentVariables()
    // {
    //     putenv('OTEL_EXPORTER_OTLP_HEADERS=x-aaa=foo,x-bbb=barf');

    //     $exporter = new ConfigOpts();

    //     $this->assertEquals(['x-aaa' => ['foo'], 'x-bbb' => ['barf']], $exporter->getHeaders());

    //     putenv('OTEL_EXPORTER_OTLP_HEADERS'); // Clear the envvar or it breaks future tests
    // }

    // public function testSetHeadersInConstructor()
    // {
    //     $exporter = new ConfigOpts('localhost:4318', true, '', 'x-aaa=foo,x-bbb=bar');

    //     $this->assertEquals(['x-aaa' => ['foo'], 'x-bbb' => ['bar']], $exporter->getHeaders());

    //     $exporter->setHeader('key', 'value');

    //     $this->assertEquals(['x-aaa' => ['foo'], 'x-bbb' => ['bar'], 'key' => ['value']], $exporter->getHeaders());
    // }

    // public function testHeadersShouldRefuseArray()
    // {
    //     $headers = [
    //         'key' => ['value'],
    //     ];

    //     $this->expectException(InvalidArgumentException::class);

    //     $headers_as_string = (new Exporter())->metadataFromHeaders($headers);
    // }

    // public function testMetadataFromHeaders()
    // {
    //     $metadata = (new Exporter())->metadataFromHeaders('key=value');
    //     $this->assertEquals(['key' => ['value']], $metadata);

    //     $metadata = (new Exporter())->metadataFromHeaders('key=value,key2=value2');
    //     $this->assertEquals(['key' => ['value'], 'key2' => ['value2']], $metadata);
    // }

    // private function isInsecure(Exporter $exporter) : bool
    // {
    //     $reflection = new \ReflectionClass($exporter);
    //     $property = $reflection->getProperty('insecure');
    //     $property->setAccessible(true);

    //     return $property->getValue($exporter);
    // }

    // public function testClientOptions()
    // {
    //     // default options
    //     $exporter = new Exporter('localhost:4318');
    //     $opts = $exporter->getClientOptions();
    //     $this->assertEquals(10, $opts['timeout']);
    //     $this->assertTrue($this->isInsecure($exporter));
    //     $this->assertFalse(array_key_exists('grpc.default_compression_algorithm', $opts));
    //     // method args
    //     $exporter = new Exporter('localhost:4318', false, '', '', true, 5);
    //     $opts = $exporter->getClientOptions();
    //     $this->assertEquals(5, $opts['timeout']);
    //     $this->assertFalse($this->isInsecure($exporter));
    //     $this->assertEquals(2, $opts['grpc.default_compression_algorithm']);
    //     // env vars
    //     putenv('OTEL_EXPORTER_OTLP_TIMEOUT=1');
    //     putenv('OTEL_EXPORTER_OTLP_COMPRESSION=1');
    //     putenv('OTEL_EXPORTER_OTLP_INSECURE=false');
    //     $exporter = new Exporter('localhost:4318');
    //     $opts = $exporter->getClientOptions();
    //     $this->assertEquals(1, $opts['timeout']);
    //     $this->assertFalse($this->isInsecure($exporter));
    //     $this->assertEquals(2, $opts['grpc.default_compression_algorithm']);
    //     putenv('OTEL_EXPORTER_OTLP_TIMEOUT');
    //     putenv('OTEL_EXPORTER_OTLP_COMPRESSION');
    //     putenv('OTEL_EXPORTER_OTLP_INSECURE');
    // }
}
