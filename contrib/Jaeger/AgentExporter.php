<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger;

use InvalidArgumentException;
use Jaeger\JaegerTransport;
use OpenTelemetry\Sdk\Trace;
use OpenTelemetry\Trace as API;

/**
 * @package OpenTelemetry\Exporter
 */
class AgentExporter implements Trace\Exporter
{

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
     *
     * @param iterable<API\Span> $spans Array of Spans
     * @return int return code, defined on the Exporter interface
     */
    public function export(iterable $spans): int
    {
        if (!$this->running) {
            return Exporter::FAILED_NOT_RETRYABLE;
        }

        if (empty($spans)) {
            return Trace\Exporter::SUCCESS;
        }

        // UDP Transport begins here after converting to thrift format span
        foreach ($spans as $span) {
            $cSpan = $this->spanConverter->convert($span);
            $this->jaegerTransport->append($cSpan, $this->serviceName);
        }

        return Trace\Exporter::SUCCESS;
    }

    public function shutdown(): void
    {
        $this->running = false;
    }
}
