<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Zipkin;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use JsonException;
use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Export\FromConnectionStringInterface;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Trace\Behavior\UsesSpanConverterTrait;
use OpenTelemetry\SDK\Trace\SpanConverterInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Throwable;

/**
 * Class ZipkinExporter - implements the export interface for data transfer via Zipkin protocol
 * @package OpenTelemetry\Exporter
 */
class Exporter implements SpanExporterInterface, FromConnectionStringInterface
{
    use LogsMessagesTrait;
    use UsesSpanConverterTrait;

    private TransportInterface $transport;

    public function __construct(
        $name,
        string $endpointUrl,
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        SpanConverterInterface $spanConverter = null
    ) {
        $this->transport = (new PsrTransportFactory($client, $requestFactory, $streamFactory))->create($endpointUrl);
        $this->setSpanConverter($spanConverter ?? new SpanConverter($name));
    }

    /**
     * @throws JsonException
     */
    protected function serializeTrace(iterable $spans): string
    {
        return json_encode(
            $this->getSpanConverter()->convert($spans),
            JSON_THROW_ON_ERROR
        );
    }

    /** @inheritDoc */
    public static function fromConnectionString(string $endpointUrl, string $name, $args = null)
    {
        return new Exporter(
            $name,
            $endpointUrl,
            HttpClientDiscovery::find(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory()
        );
    }

    public function export(iterable $spans, ?CancellationInterface $cancellation = null): FutureInterface
    {
        return $this->transport
            ->send($this->serializeTrace($spans), 'application/json', $cancellation)
            ->map(static fn (): bool => true)
            ->catch(static function (Throwable $throwable): bool {
                self::logError('Export failure', ['exception' => $throwable]);

                return false;
            });
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        return $this->transport->shutdown($cancellation);
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return $this->transport->forceFlush($cancellation);
    }
}
