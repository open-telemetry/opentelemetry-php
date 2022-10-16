<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Otlp;

use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransport;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory
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
