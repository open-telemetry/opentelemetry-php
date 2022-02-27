<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

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
        $name,
        string $endpointUrl,
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

        if (!isset($parsedDsn['port'])) {
            if ($parsedDsn['scheme'] === 'https') {
                $parsedDsn['port'] = 443;
            } else {
                $parsedDsn['port'] = 80;
            }
        }

        $this->sender = new ThriftHttpSender(
            $client,
            $requestFactory,
            $streamFactory,
            $name,
            $parsedDsn['host'],
            $parsedDsn['port'],
            isset($parsedDsn['path']) ? $parsedDsn['path'] : '', //Matching THttpClient's default
            isset($parsedDsn['scheme']) ? $parsedDsn['scheme'] : 'http', //Matching THttpClient's default
            $endpointUrl
        );

        $this->spanConverter = new SpanConverter();
    }

    /**
     * @psalm-return SpanExporterInterface::STATUS_*
     */
    public function doExport(iterable $spans): int
    {
        $this->sender->send($this->spanConverter->convert($spans));

        return SpanExporterInterface::STATUS_SUCCESS;
    }

    /** @inheritDoc */
    public static function fromConnectionString(string $endpointUrl, string $name, $args = null): HttpCollectorExporter
    {
        return new HttpCollectorExporter(
            $name,
            $endpointUrl
        );
    }
}
