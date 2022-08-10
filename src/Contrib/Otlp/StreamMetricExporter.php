<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use function fflush;
use function fopen;
use function fwrite;
use function is_string;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use const STDOUT;
use function strlen;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/sdk_exporters/stdout.md#opentelemetry-metrics-exporter---standard-output
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/experimental/serialization/json.md#json-file-serialization
 */
final class StreamMetricExporter implements MetricExporterInterface
{
    /**
     * @var resource|null
     */
    private $stream;
    /**
     * @var string|Temporality|null
     */
    private $temporality;

    /**
     * @param string|resource $stream filename or stream to write to
     * @param string|Temporality|null $temporality
     */
    public function __construct($stream = STDOUT, $temporality = null)
    {
        $this->stream = is_string($stream)
            ? @fopen($stream, 'ab')
            : $stream;
        $this->temporality = $temporality;
    }

    public function temporality(MetricMetadataInterface $metric)
    {
        return $this->temporality ?? $metric->temporality();
    }

    public function export(iterable $batch): bool
    {
        if (!$this->stream) {
            return false;
        }

        $payload = (new MetricConverter())->convert($batch)->serializeToJsonString();
        $payload .= "\n";

        return @fwrite($this->stream, $payload) === strlen($payload);
    }

    public function shutdown(): bool
    {
        if (!$this->stream) {
            return false;
        }

        $flush = @fflush($this->stream);
        $this->stream = null;

        return $flush;
    }

    public function forceFlush(): bool
    {
        if (!$this->stream) {
            return false;
        }

        return @fflush($this->stream);
    }
}
