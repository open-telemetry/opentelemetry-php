<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

use InvalidArgumentException;
use OpenTelemetry\SDK\Trace\Behavior\SpanExporterTrait;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

/**
 * @package OpenTelemetry\Exporter
 */
class AgentExporter implements SpanExporterInterface
{
    use SpanExporterTrait;

    private string $serviceName;

    private SpanConverter $spanConverter;

    private JaegerTransport $jaegerTransport;

    public function __construct(
        $name,
        string $endpointUrl
    ) {
        $parsedDsn = parse_url($endpointUrl);

        if (!is_array($parsedDsn)) {
            throw new InvalidArgumentException('Unable to parse provided DSN');
        }

        if (!isset($parsedDsn['host']) || !isset($parsedDsn['port'])) {
            throw new InvalidArgumentException('Endpoint should have host, port');
        }

        $this->serviceName = $name;

        $this->spanConverter = new SpanConverter();
        $this->jaegerTransport = new JaegerTransport($parsedDsn['host'], $parsedDsn['port']);
    }

    public function closeAgentConnection(): void
    {
        $this->jaegerTransport->close();
    }

    /**
     * @psalm-return SpanExporterInterface::STATUS_*
     */
    public function doExport(iterable $spans): int
    {
        // UDP Transport begins here after converting to thrift format span
        foreach ($spans as $span) {
            $this->jaegerTransport->append(
                $this->spanConverter->convert([$span])[0],
                $this->serviceName
            );
        }

        return SpanExporterInterface::STATUS_SUCCESS;
    }

    /** @inheritDoc */
    public static function fromConnectionString(string $endpointUrl, string $name, $args = null): AgentExporter
    {
        return new AgentExporter(
            $name,
            $endpointUrl
        );
    }
}
