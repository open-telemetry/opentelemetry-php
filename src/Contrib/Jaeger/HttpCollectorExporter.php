<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use InvalidArgumentException;
use OpenTelemetry\SDK\Trace\Behavior\SpanExporterTrait;
use OpenTelemetry\SDK\Trace\Behavior\UsesSpanConverterTrait;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class HttpCollectorExporter implements SpanExporterInterface
{
    use UsesSpanConverterTrait;
    use SpanExporterTrait;

    private SpanConverter $spanConverter;

    private ThriftHttpSender $sender;

    public function __construct(
        string $endpointUrl,
        string $name,
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $parsedDsn = parse_url($endpointUrl);

        if (!is_array($parsedDsn)) {
            throw new InvalidArgumentException('Unable to parse provided DSN');
        }

        if (!isset($parsedDsn['host'])) {
            throw new InvalidArgumentException('Endpoint should have host');
        }

        $this->sender = new ThriftHttpSender(
            $client,
            $requestFactory,
            $streamFactory,
            $name,
            $endpointUrl
        );

        $this->spanConverter = new SpanConverter();
    }

    /**
     * @psalm-return SpanExporterInterface::STATUS_*
     */
    public function doExport(iterable $spans): int
    {
        $this->sender->send(
            $this->spanConverter->convert($spans)
        );

        return SpanExporterInterface::STATUS_SUCCESS;
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
