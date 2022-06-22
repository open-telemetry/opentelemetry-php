<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Behavior;

use GuzzleHttp\Exception\ClientException;
use OpenTelemetry\API\Common\Event\Dispatcher;
use OpenTelemetry\SDK\Trace\Behavior\HttpSpanExporterTrait;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Throwable;

/**
 * @covers \OpenTelemetry\SDK\Trace\Behavior\HttpSpanExporterTrait
 */
class HttpSpanExporterTraitTest extends TestCase
{
    private SpanExporterInterface $exporter;
    private ClientInterface $client;
    private EventDispatcherInterface $dispatcher;

    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function setUp(): void
    {
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $this->client = $this->createMock(ClientInterface::class);
        $this->exporter = $this->createExporter();
        $this->exporter->setRequest($request); //@phpstan-ignore-line
        $this->exporter->setClient($this->client); //@phpstan-ignore-line
        Dispatcher::setInstance($this->dispatcher);
    }

    public function tearDown(): void
    {
        Dispatcher::unset();
    }

    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function test_export_with_client_exception_generates_error_event(): void
    {
        $this->client->expects($this->once())->method('sendRequest')->willThrowException($this->createMock(ClientException::class)); //@phpstan-ignore-line
        $this->dispatcher->expects($this->once())->method('dispatch'); //@phpstan-ignore-line
        $this->exporter->export([$this->createMock(SpanDataInterface::class)]);
    }

    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function test_export_with_throwable_generates_error_event(): void
    {
        $this->client->expects($this->once())->method('sendRequest')->willThrowException($this->createMock(Throwable::class)); //@phpstan-ignore-line
        $this->dispatcher->expects($this->once())->method('dispatch'); //@phpstan-ignore-line
        $this->exporter->export([$this->createMock(SpanDataInterface::class)]);
    }

    private function createExporter(): SpanExporterInterface
    {
        return new class() implements SpanExporterInterface {
            use HttpSpanExporterTrait;
            private RequestInterface $request;
            public function setRequest(RequestInterface $request): void
            {
                $this->request = $request;
            }
            public function setClient(ClientInterface $client): void
            {
                $this->client = $client;
            }
            public function serializeTrace(iterable $spans): string
            {
                return '';
            }
            public static function fromConnectionString(string $endpointUrl, string $name, string $args)
            {
                return new \stdClass();
            }
            public function marshallRequest(iterable $spans): RequestInterface
            {
                return $this->request;
            }
        };
    }
}
