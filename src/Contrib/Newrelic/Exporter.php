<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Newrelic;

use Exception;
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
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Throwable;

/**
 * Class NewrelicExporter - implements the export interface for data transfer via Newrelic protocol
 * @package OpenTelemetry\Exporter
 *
 * This is an experimental, non-supported exporter.
 * It will send PHP Otel trace data end to end across the internet to a functional backend.
 * Needs a license key to connect.  For a free account/key, go to: https://newrelic.com/signup/
 */
class Exporter implements SpanExporterInterface, FromConnectionStringInterface
{
    use LogsMessagesTrait;
    use UsesSpanConverterTrait;

    private const DATA_FORMAT_VERSION_DEFAULT = '1';

    private TransportInterface $transport;
    private string $name;
    private string $endpointUrl;

    public function __construct(
        $name,
        string $endpointUrl,
        string $licenseKey,
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        SpanConverter $spanConverter = null,
        string $dataFormatVersion = Exporter::DATA_FORMAT_VERSION_DEFAULT
    ) {
        $this->transport = (new PsrTransportFactory($client, $requestFactory, $streamFactory))->create($endpointUrl, [
            'Api-Key' => $licenseKey,
            'Data-Format' => 'newrelic',
            'Data-Format-Version' => $dataFormatVersion,
        ]);
        $this->name = $name;
        $this->endpointUrl = $endpointUrl;
        $this->setSpanConverter($spanConverter ?? new SpanConverter($name));
    }

    /**
     * @throws JsonException
     */
    protected function serializeTrace(iterable $spans): string
    {
        return json_encode($this->convert($spans), JSON_THROW_ON_ERROR);
    }

    private function convert(iterable $spans): array
    {
        $commonAttributes = ['attributes' => [ 'service.name' => $this->name,
            'host' => $this->endpointUrl, ]];

        return [[ 'common' => $commonAttributes,
            'spans' => $this->getSpanConverter()->convert($spans), ]];
    }

    /** @inheritDoc */
    public static function fromConnectionString(string $endpointUrl, string $name, $args)
    {
        if (!is_string($args)) {
            throw new Exception('Invalid license key.');
        }

        return new Exporter(
            $name,
            $endpointUrl,
            $args,
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
