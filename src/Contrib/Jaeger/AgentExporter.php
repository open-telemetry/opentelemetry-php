<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

use InvalidArgumentException;
use OpenTelemetry\Contrib\Jaeger\JaegerTransport;
use OpenTelemetry\SDK\Trace\Behavior\SpanExporterTrait;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

/**
 * @package OpenTelemetry\Exporter
 */
class AgentExporter implements SpanExporterInterface
{
    use SpanExporterTrait;

    /**
     * @var string
     */
    private $endpointUrl;

    /**
     * @var SpanConverter
     */
    private $spanConverter;

    /**
     * @var bool
     */
    private $running = true;

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

        $this->endpointUrl = $endpointUrl;
        $this->serviceName = $name;
        $this->spanConverter = $spanConverter ?? new SpanConverter($name);
        $this->jaegerTransport = new JaegerTransport($parsedDsn['host'], $parsedDsn['port']);
    }

    /**
     * @psalm-return SpanExporterInterface::STATUS_*
     */
    public function doExport(iterable $spans): int
    {
        // UDP Transport begins here after converting to thrift format span
        foreach ($spans as $span) {
            $cSpan = $this->spanConverter->convert($span);
            $this->jaegerTransport->append($cSpan, $this->serviceName);
        }

        return SpanExporterInterface::STATUS_SUCCESS;
    }

    /** @inheritDoc */
    public static function fromConnectionString(string $endpointUrl, string $name, $args = null)
    {
        return new AgentExporter(
            $name,
            $endpointUrl
        );
    }
} 