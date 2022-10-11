<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\OtlpHttp;

use AssertWell\PHPUnitGlobalState\EnvironmentVariables;
use OpenTelemetry\Contrib\OtlpHttp\OtlpHttpTransportFactory;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransport;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Contrib\OtlpHttp\OtlpHttpTransportFactory
 */
class OtlpHttpTransportFactoryTest extends TestCase
{
    use EnvironmentVariables;

    private OtlpHttpTransportFactory $factory;

    public function setUp(): void
    {
        $this->factory = new OtlpHttpTransportFactory();
    }

    public function tearDown(): void
    {
        self::restoreEnvironmentVariables();
    }

    public function test_factory_creates_psr_transport(): void
    {
        $transport = $this->factory->create('http://example.com');
        $this->assertInstanceOf(PsrTransport::class, $transport);
    }

    /**
     * @dataProvider exporterInvalidEndpointDataProvider
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function test_exporter_refuses_invalid_endpoint($endpoint): void
    {
        $this->setEnvironmentVariable('OTEL_EXPORTER_OTLP_ENDPOINT', $endpoint);
        $this->expectException(\InvalidArgumentException::class);

        $this->factory->create();
    }

    public function exporterInvalidEndpointDataProvider(): array
    {
        return [
            'Not a url' => ['not a url'],
            'Grpc Scheme' => ['grpc://localhost:4317'],
        ];
    }
}
