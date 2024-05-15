<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\SpanExporter;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\ErrorFuture;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\Tests\Unit\SDK\Util\SpanData;

/**
 * @psalm-suppress UndefinedInterfaceMethod
 */
abstract class AbstractExporterTestCase extends MockeryTestCase
{
    protected TransportInterface $transport;
    protected FutureInterface $future;

    public function setUp(): void
    {
        Logging::disable();
        $this->future = Mockery::mock(FutureInterface::class);
        $this->future->allows([
            'map' => $this->future,
            'catch' => $this->future,
        ]);
        $this->transport = Mockery::mock(TransportInterface::class)->makePartial();
        $this->transport->allows(['send' => $this->future]);
    }

    /**
     * Must be implemented by concrete TestCases
     */
    abstract public function createExporterWithTransport(TransportInterface $transport): SpanExporterInterface;

    /**
     * Must be implemented by concrete TestCases
     */
    abstract public function getExporterClass(): string;

    protected function createExporter(): SpanExporterInterface
    {
        return $this->createExporterWithTransport($this->transport);
    }

    public function test_shutdown(): void
    {
        $this->transport->shouldReceive('shutdown')->andReturn(true);

        $this->assertTrue(
            $this->createExporter()->shutdown()
        );
    }

    public function test_force_flush(): void
    {
        $this->transport->shouldReceive('forceFlush')->andReturn(true);

        $this->assertTrue(
            $this->createExporter()->forceFlush()
        );
    }

    /**
     * @dataProvider futureProvider
     */
    public function test_export(FutureInterface $future, bool $expected): void
    {
        $transport = Mockery::mock(TransportInterface::class);
        $exporter = $this->createExporterWithTransport($transport);
        $span = $this->createMock(SpanData::class);
        $transport->shouldReceive('send')->andReturn($future);

        $this->assertSame($expected, $exporter->export([$span])->await());
    }

    public static function futureProvider(): array
    {
        return [
            'error future' => [
                new ErrorFuture(new \Exception('foo')),
                false,
            ],
            'completed future' => [
                new CompletedFuture([]),
                true,
            ],
        ];
    }
}
