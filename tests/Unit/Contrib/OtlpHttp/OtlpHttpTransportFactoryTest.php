<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\OtlpHttp;

use OpenTelemetry\Contrib\OtlpHttp\OtlpHttpTransportFactory;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransport;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Contrib\OtlpHttp\OtlpHttpTransportFactory
 */
class OtlpHttpTransportFactoryTest extends TestCase
{
    private OtlpHttpTransportFactory $factory;

    public function setUp(): void
    {
        $this->factory = new OtlpHttpTransportFactory();
    }

    public function test_factory_creates_psr_transport(): void
    {
        $transport = $this->factory->create('http://example.com', 'application/x-protobuf');
        $this->assertInstanceOf(PsrTransport::class, $transport);
    }
}
