<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

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
        $parsedEndpoint = (new ParsedEndpointUrl($endpointUrl))
                                ->validateHost() //This is because the host is required downstream
                                ->validatePort(); //This is because the port is required downstream

        $this->serviceName = $name;

        $this->spanConverter = new SpanConverter();
        $this->jaegerTransport = new JaegerTransport($parsedEndpoint);
    }

    public function closeAgentConnection(): void
    {
        $this->jaegerTransport->close();
    }

    public function doExport(iterable $spans): bool
    {
        // UDP Transport begins here after converting to thrift format span
        foreach ($spans as $span) {
            $this->jaegerTransport->append(
                $this->spanConverter->convert([$span])[0],
                $this->serviceName
            );
        }

        return true;
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
