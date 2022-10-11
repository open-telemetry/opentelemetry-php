<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use Throwable;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/sdk_exporters/stdout.md#opentelemetry-metrics-exporter---standard-output
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/experimental/serialization/json.md#json-file-serialization
 */
final class MetricExporter implements MetricExporterInterface
{
    use LogsMessagesTrait;

    private TransportInterface $transport;
    private string $protocol;
    /**
     * @var string|Temporality|null
     */
    private $temporality;

    /**
     * @param string|Temporality|null $temporality
     */
    public function __construct(TransportInterface $transport, string $protocol, $temporality = null)
    {
        $this->transport = $transport;
        $this->protocol = $protocol;
        $this->temporality = $temporality;
    }

    public function temporality(MetricMetadataInterface $metric)
    {
        return $this->temporality ?? $metric->temporality();
    }

    public function export(iterable $batch): bool
    {
        $request = (new MetricConverter())->convert($batch);
        $payload = Converter::encode($request, $this->protocol);

        return $this->transport
            ->send($payload, Converter::contentType($this->protocol))
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
