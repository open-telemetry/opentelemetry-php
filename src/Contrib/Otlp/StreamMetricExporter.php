<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use function is_string;
use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransport;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use const STDOUT;
use Throwable;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/sdk_exporters/stdout.md#opentelemetry-metrics-exporter---standard-output
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/experimental/serialization/json.md#json-file-serialization
 */
final class StreamMetricExporter implements MetricExporterInterface
{
    use LogsMessagesTrait;

    private TransportInterface $transport;
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
        $this->transport = is_string($stream)
            ? (new StreamTransportFactory())->create($stream)
            : new StreamTransport($stream);
        $this->temporality = $temporality;
    }

    public function temporality(MetricMetadataInterface $metric)
    {
        return $this->temporality ?? $metric->temporality();
    }

    public function export(iterable $batch): bool
    {
        $payload = (new MetricConverter())->convert($batch)->serializeToJsonString();
        $payload .= "\n";

        return $this->transport
            ->send($payload, 'application/x-ndjson')
            ->map(static fn (): bool => true)
            ->catch(static function (Throwable $throwable): bool {
                self::logError('Export failure', ['exception' => $throwable]);

                return false;
            })
            ->await();
    }

    public function shutdown(): bool
    {
        return $this->transport->shutdown();
    }

    public function forceFlush(): bool
    {
        return $this->transport->forceFlush();
    }
}
