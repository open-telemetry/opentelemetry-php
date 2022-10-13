<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use Opentelemetry\Proto\Collector\Metrics\V1\ExportMetricsServiceResponse;
use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use Throwable;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/sdk_exporters/stdout.md#opentelemetry-metrics-exporter---standard-output
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/experimental/serialization/json.md#json-file-serialization
 * @psalm-import-type SUPPORTED_CONTENT_TYPES from ProtobufSerializer
 */
final class MetricExporter implements MetricExporterInterface
{
    use LogsMessagesTrait;

    private TransportInterface $transport;
    private ProtobufSerializer $serializer;
    /**
     * @var string|Temporality|null
     */
    private $temporality;

    /**
     * @param string|Temporality|null $temporality
     *
     * @psalm-param TransportInterface<SUPPORTED_CONTENT_TYPES> $transport
     */
    public function __construct(TransportInterface $transport, $temporality = null)
    {
        $this->transport = $transport;
        $this->serializer = ProtobufSerializer::forTransport($transport);
        $this->temporality = $temporality;
    }

    public function temporality(MetricMetadataInterface $metric)
    {
        return $this->temporality ?? $metric->temporality();
    }

    public function export(iterable $batch): bool
    {
        return $this->transport
            ->send($this->serializer->serialize((new MetricConverter())->convert($batch)))
            ->map(function (?string $payload): bool {
                if ($payload === null) {
                    return true;
                }

                $serviceResponse = new ExportMetricsServiceResponse();
                $this->serializer->hydrate($serviceResponse, $payload);

                $partialSuccess = $serviceResponse->getPartialSuccess();
                if ($partialSuccess !== null && $partialSuccess->getRejectedDataPoints()) {
                    self::logError('Export partial success', [
                        'rejected_data_points' => $partialSuccess->getRejectedDataPoints(),
                        'error_message' => $partialSuccess->getErrorMessage(),
                    ]);

                    return false;
                }
                if ($partialSuccess !== null && $partialSuccess->getErrorMessage()) {
                    self::logWarning('Export success with warnings/suggestions', ['error_message' => $partialSuccess->getErrorMessage()]);
                }

                return true;
            })
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
