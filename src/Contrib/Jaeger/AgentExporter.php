<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\Behavior\SpanExporterTrait;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use OpenTelemetry\SemConv\ResourceAttributes;

/**
 * @package OpenTelemetry\Exporter
 * @deprecated Jaeger exports are archived, and suggest switching to otel exporters
 * @see https://github.com/open-telemetry/opentelemetry-specification/pull/2859
 */
class AgentExporter implements SpanExporterInterface
{
    use SpanExporterTrait;

    private SpanConverter $spanConverter;

    private JaegerTransport $jaegerTransport;

    private string $defaultServiceName;

    public function __construct(
        string $endpointUrl
    ) {
        $parsedEndpoint = (new ParsedEndpointUrl($endpointUrl))
                                ->validateHost() //This is because the host is required downstream
                                ->validatePort(); //This is because the port is required downstream

        $this->spanConverter = new SpanConverter();
        $this->jaegerTransport = new JaegerTransport($parsedEndpoint);
        $this->defaultServiceName = ResourceInfoFactory::defaultResource()->getAttributes()->get(ResourceAttributes::SERVICE_NAME);
    }

    public function closeAgentConnection(): void
    {
        $this->jaegerTransport->close();
    }

    public function doExport(iterable $spans): bool
    {
        // UDP Transport begins here after converting to thrift format span
        foreach ($spans as $span) {
            $serviceName =  $span->getResource()->getAttributes()->get(ResourceAttributes::SERVICE_NAME)
                            ??
                            $this->defaultServiceName;
            $this->jaegerTransport->append(
                $this->spanConverter->convert([$span])[0],
                $serviceName
            );
        }

        return true;
    }
}
