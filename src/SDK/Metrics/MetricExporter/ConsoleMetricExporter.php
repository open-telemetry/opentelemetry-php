<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricExporter;

use function get_object_vars;
use function is_array;
use function is_finite;
use function is_float;
use function is_nan;
use function is_object;
use function iterator_to_array;
use JsonException;
use JsonSerializable;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Metrics\AggregationTemporalitySelectorInterface;
use OpenTelemetry\SDK\Metrics\Data\Metric;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use OpenTelemetry\SDK\Metrics\PushMetricExporterInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use Traversable;

/**
 * Writes metrics as human-readable JSON.
 *
 * The output format is unspecified, is not OTLP and may change between releases. Use it for
 * debugging and learning, not for parsing or as input to other tools.
 *
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/metrics/sdk_exporters/stdout.md
 */
class ConsoleMetricExporter implements PushMetricExporterInterface, AggregationTemporalitySelectorInterface
{
    use LogsMessagesTrait;

    private readonly TransportInterface $transport;

    public function __construct(
        private readonly Temporality|string|AggregationTemporalitySelectorInterface|null $temporality = null,
        ?TransportInterface $transport = null,
    ) {
        $this->transport = $transport ?? (new StreamTransportFactory())->create('php://stdout', 'application/json');
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function temporality(MetricMetadataInterface $metric): Temporality|string|null
    {
        if ($this->temporality instanceof AggregationTemporalitySelectorInterface) {
            return $this->temporality->temporality($metric);
        }

        return $this->temporality ?? $metric->temporality();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function export(iterable $batch): bool
    {
        $resource = null;
        $scope = null;
        foreach ($batch as $metric) {
            /** @var Metric $metric */
            if (!$resource) {
                $resource = $this->convertResource($metric->resource);
            }
            if (!$scope) {
                $scope = $this->convertInstrumentationScope($metric->instrumentationScope);
                $scope['metrics'] = [];
            }
            $scope['metrics'][] = $this->convertMetric($metric);
        }
        $output = [
            'resource' => $resource,
            'scope' => $scope,
        ];

        try {
            $payload = json_encode(self::normalize($output), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT) . PHP_EOL;
        } catch (JsonException $e) {
            self::logWarning('Error converting metrics: ' . $e->getMessage());

            return false;
        }

        return $this->transport->send($payload)
            ->map(static fn (): bool => true)
            ->catch(static fn (): bool => false)
            ->await();
    }

    #[\Override]
    public function shutdown(): bool
    {
        return $this->transport->shutdown();
    }

    #[\Override]
    public function forceFlush(): bool
    {
        return $this->transport->forceFlush();
    }

    /**
     * JSON has no NaN or Infinity, so these use the protobuf JSON spellings.
     */
    private static function normalize(mixed $value): mixed
    {
        if (is_float($value) && !is_finite($value)) {
            return match (true) {
                is_nan($value) => 'NaN',
                $value > 0 => 'Infinity',
                default => '-Infinity',
            };
        }
        if ($value instanceof JsonSerializable) {
            $value = $value->jsonSerialize();
        } elseif ($value instanceof Traversable) {
            $value = iterator_to_array($value, false);
        } elseif (is_object($value)) {
            $value = get_object_vars($value);
        }
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = self::normalize($item);
            }
        }

        return $value;
    }

    private function convertMetric(Metric $metric): array
    {
        return [
            'name' => $metric->name,
            'description' => $metric->description,
            'unit' => $metric->unit,
            'data' => $metric->data,
        ];
    }

    private function convertResource(ResourceInfo $resource): array
    {
        return [
            'attributes' => $resource->getAttributes()->toArray(),
            'dropped_attributes_count' => $resource->getAttributes()->getDroppedAttributesCount(),
        ];
    }

    private function convertInstrumentationScope(InstrumentationScopeInterface $scope): array
    {
        return [
            'name' => $scope->getName(),
            'version' => $scope->getVersion(),
            'attributes' => $scope->getAttributes()->toArray(),
            'dropped_attributes_count' => $scope->getAttributes()->getDroppedAttributesCount(),
            'schema_url' => $scope->getSchemaUrl(),
        ];
    }
}
