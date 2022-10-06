<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use OpenTelemetry\SDK\Common\Export\FromConnectionStringInterface;
use OpenTelemetry\SDK\Trace\Behavior\SpanExporterTrait;
use OpenTelemetry\SDK\Trace\Behavior\UsesSpanConverterTrait;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class HttpCollectorExporter implements SpanExporterInterface, FromConnectionStringInterface
{
    use UsesSpanConverterTrait;
    use SpanExporterTrait;

    private HttpSender $sender;

    public function __construct(
        string $endpointUrl,
        string $name,
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $parsedEndpoint = (new ParsedEndpointUrl($endpointUrl))
                                ->validateHost(); //This is because the host is required downstream

        $this->sender = new HttpSender(
            $client,
            $requestFactory,
            $streamFactory,
            $name,
            $parsedEndpoint
        );
    }

    public function doExport(iterable $spans): bool
    {
        $this->sender->send($spans);

        return true;
    }

    /** @inheritDoc */
    public static function fromConnectionString(string $endpointUrl, string $name, $args = null): HttpCollectorExporter
    {
        return new HttpCollectorExporter(
            $endpointUrl,
            $name,
            HttpClientDiscovery::find(),
            Psr17FactoryDiscovery::findRequestFactory(),
            Psr17FactoryDiscovery::findStreamFactory()
        );
    }
}
