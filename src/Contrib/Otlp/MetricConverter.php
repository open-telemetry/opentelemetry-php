<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use function is_float;
use function is_int;
use function method_exists;
use Opentelemetry\Proto\Collector\Metrics\V1\ExportMetricsServiceRequest;
use Opentelemetry\Proto\Common\V1\InstrumentationScope;
use Opentelemetry\Proto\Common\V1\KeyValue;
use Opentelemetry\Proto\Metrics\V1\AggregationTemporality;
use Opentelemetry\Proto\Metrics\V1\Exemplar;
use Opentelemetry\Proto\Metrics\V1\Gauge;
use Opentelemetry\Proto\Metrics\V1\Histogram;
use Opentelemetry\Proto\Metrics\V1\HistogramDataPoint;
use Opentelemetry\Proto\Metrics\V1\Metric;
use Opentelemetry\Proto\Metrics\V1\NumberDataPoint;
use Opentelemetry\Proto\Metrics\V1\ResourceMetrics;
use Opentelemetry\Proto\Metrics\V1\ScopeMetrics;
use Opentelemetry\Proto\Metrics\V1\Sum;
use Opentelemetry\Proto\Resource\V1\Resource as Resource_;
use OpenTelemetry\SDK;
use function serialize;

final class MetricConverter
{

    /**
     * @param iterable<Sdk\Metrics\Data\Metric> $batch
     */
    public function convert(iterable $batch): ExportMetricsServiceRequest
    {
        $pExportMetricsServiceRequest = new ExportMetricsServiceRequest();

        $resourceMetrics = [];
        $resourceCache = [];
        $scopeMetrics = [];
        $scopeCache = [];
        foreach ($batch as $metric) {
            $resource = $metric->resource;
            $instrumentationScope = $metric->instrumentationScope;

            $resourceId = $resourceCache[spl_object_id($resource)] ??= serialize([
                $resource->getSchemaUrl(),
                $resource->getAttributes()->toArray(),
                $resource->getAttributes()->getDroppedAttributesCount(),
            ]);
            $instrumentationScopeId = $scopeCache[spl_object_id($instrumentationScope)] ??= serialize([
                $instrumentationScope->getName(),
                $instrumentationScope->getVersion(),
                $instrumentationScope->getSchemaUrl(),
                $instrumentationScope->getAttributes()->toArray(),
                $instrumentationScope->getAttributes()->getDroppedAttributesCount(),
            ]);

            if (!$pResourceMetrics = $resourceMetrics[$resourceId] ?? null) {
                /** @psalm-suppress InvalidArgument */
                $pExportMetricsServiceRequest->getResourceMetrics()[]
                    = $resourceMetrics[$resourceId]
                    = $pResourceMetrics
                    = $this->convertResourceMetrics($resource);
            }

            if (!$pScopeMetrics = $scopeMetrics[$resourceId][$instrumentationScopeId] ?? null) {
                /** @psalm-suppress InvalidArgument */
                $pResourceMetrics->getScopeMetrics()[]
                    = $scopeMetrics[$resourceId][$instrumentationScopeId]
                    = $pScopeMetrics
                    = $this->convertScopeMetrics($instrumentationScope);
            }

            /** @psalm-suppress InvalidArgument */
            $pScopeMetrics->getMetrics()[] = $this->convertMetric($metric);
        }

        return $pExportMetricsServiceRequest;
    }

    private function convertResourceMetrics(SDK\Resource $resource): ResourceMetrics
    {
        $pResourceMetrics = new ResourceMetrics();
        $pResource = new Resource_();
        $this->setAttributes($pResource, $resource->getAttributes());
        $pResourceMetrics->setResource($pResource);
        $pResourceMetrics->setSchemaUrl((string) $resource->getSchemaUrl());

        return $pResourceMetrics;
    }

    private function convertScopeMetrics(SDK\InstrumentationScope $instrumentationScope): ScopeMetrics
    {
        $pScopeMetrics = new ScopeMetrics();
        $pInstrumentationScope = new InstrumentationScope();
        $pInstrumentationScope->setName($instrumentationScope->getName());
        $pInstrumentationScope->setVersion((string) $instrumentationScope->getVersion());
        $pScopeMetrics->setScope($pInstrumentationScope);
        $pScopeMetrics->setSchemaUrl((string) $instrumentationScope->getSchemaUrl());

        return $pScopeMetrics;
    }

    private function convertMetric(Sdk\Metrics\Data\Metric $metric): Metric
    {
        $pMetric = new Metric();
        $pMetric->setName($metric->name);
        $pMetric->setDescription((string) $metric->description);
        $pMetric->setUnit((string) $metric->unit);
        if ($metric->data instanceof SDK\Metrics\Data\Gauge) {
            $pMetric->setGauge($this->convertGauge($metric->data));
        }
        if ($metric->data instanceof SDK\Metrics\Data\Histogram) {
            $pMetric->setHistogram($this->convertHistogram($metric->data));
        }
        if ($metric->data instanceof SDK\Metrics\Data\Sum) {
            $pMetric->setSum($this->convertSum($metric->data));
        }

        return $pMetric;
    }

    private function convertTemporality($temporality): int
    {
        switch ($temporality) {
            case SDK\Metrics\Data\Temporality::DELTA:
                return AggregationTemporality::AGGREGATION_TEMPORALITY_DELTA;
            case SDK\Metrics\Data\Temporality::CUMULATIVE:
                return AggregationTemporality::AGGREGATION_TEMPORALITY_CUMULATIVE;
        }

        return AggregationTemporality::AGGREGATION_TEMPORALITY_UNSPECIFIED;
    }

    private function convertGauge(Sdk\Metrics\Data\Gauge $gauge): Gauge
    {
        $pGauge = new Gauge();
        foreach ($gauge->dataPoints as $dataPoint) {
            /** @psalm-suppress InvalidArgument */
            $pGauge->getDataPoints()[] = $this->convertNumberDataPoint($dataPoint);
        }

        return $pGauge;
    }

    private function convertHistogram(Sdk\Metrics\Data\Histogram $histogram): Histogram
    {
        $pHistogram = new Histogram();
        foreach ($histogram->dataPoints as $dataPoint) {
            /** @psalm-suppress InvalidArgument */
            $pHistogram->getDataPoints()[] = $this->convertHistogramDataPoint($dataPoint);
        }
        $pHistogram->setAggregationTemporality($this->convertTemporality($histogram->temporality));

        return $pHistogram;
    }

    private function convertSum(Sdk\Metrics\Data\Sum $sum): Sum
    {
        $pSum = new Sum();
        foreach ($sum->dataPoints as $dataPoint) {
            /** @psalm-suppress InvalidArgument */
            $pSum->getDataPoints()[] = $this->convertNumberDataPoint($dataPoint);
        }
        $pSum->setAggregationTemporality($this->convertTemporality($sum->temporality));
        $pSum->setIsMonotonic($sum->monotonic);

        return $pSum;
    }

    private function convertNumberDataPoint(Sdk\Metrics\Data\NumberDataPoint $dataPoint): NumberDataPoint
    {
        $pNumberDataPoint = new NumberDataPoint();
        $this->setAttributes($pNumberDataPoint, $dataPoint->attributes);
        $pNumberDataPoint->setStartTimeUnixNano($dataPoint->startTimestamp);
        $pNumberDataPoint->setTimeUnixNano($dataPoint->timestamp);
        if (is_int($dataPoint->value)) {
            $pNumberDataPoint->setAsInt($dataPoint->value);
        }
        if (is_float($dataPoint->value)) {
            $pNumberDataPoint->setAsDouble($dataPoint->value);
        }
        foreach ($dataPoint->exemplars as $exemplar) {
            /** @psalm-suppress InvalidArgument */
            $pNumberDataPoint->getExemplars()[] = $this->convertExemplar($exemplar);
        }

        return $pNumberDataPoint;
    }

    private function convertHistogramDataPoint(Sdk\Metrics\Data\HistogramDataPoint $dataPoint): HistogramDataPoint
    {
        $pHistogramDataPoint = new HistogramDataPoint();
        $this->setAttributes($pHistogramDataPoint, $dataPoint->attributes);
        $pHistogramDataPoint->setStartTimeUnixNano($dataPoint->startTimestamp);
        $pHistogramDataPoint->setTimeUnixNano($dataPoint->timestamp);
        $pHistogramDataPoint->setCount($dataPoint->count);
        $pHistogramDataPoint->setSum($dataPoint->sum);
        /** @phpstan-ignore-next-line */
        $pHistogramDataPoint->setBucketCounts($dataPoint->bucketCounts);
        /** @phpstan-ignore-next-line */
        $pHistogramDataPoint->setExplicitBounds($dataPoint->explicitBounds);
        foreach ($dataPoint->exemplars as $exemplar) {
            /** @psalm-suppress InvalidArgument */
            $pHistogramDataPoint->getExemplars()[] = $this->convertExemplar($exemplar);
        }

        return $pHistogramDataPoint;
    }

    private function convertExemplar(Sdk\Metrics\Data\Exemplar $exemplar): Exemplar
    {
        $pExemplar = new Exemplar();
        $this->setFilteredAttributes($pExemplar, $exemplar->attributes);
        $pExemplar->setTimeUnixNano($exemplar->timestamp);
        $pExemplar->setSpanId((string) $exemplar->spanId);
        $pExemplar->setTraceId((string) $exemplar->traceId);
        if (is_int($exemplar->value)) {
            $pExemplar->setAsInt($exemplar->value);
        }
        if (is_float($exemplar->value)) {
            $pExemplar->setAsDouble($exemplar->value);
        }

        return $pExemplar;
    }

    /**
     * @param Resource_|NumberDataPoint|HistogramDataPoint $pElement
     */
    private function setAttributes($pElement, SDK\Attributes $attributes): void
    {
        foreach ($attributes as $key => $value) {
            /** @psalm-suppress InvalidArgument */
            $pElement->getAttributes()[] = $pAttribute = new KeyValue();
            $pAttribute->setKey($key);
            $pAttribute->setValue(AttributesConverter::convertAnyValue($value));
        }
        if (method_exists($pElement, 'setDroppedAttributesCount')) {
            $pElement->setDroppedAttributesCount($attributes->getDroppedAttributesCount());
        }
    }

    private function setFilteredAttributes(Exemplar $pElement, SDK\Attributes $attributes): void
    {
        foreach ($attributes as $key => $value) {
            /** @psalm-suppress InvalidArgument */
            $pElement->getFilteredAttributes()[] = $pAttribute = new KeyValue();
            $pAttribute->setKey($key);
            $pAttribute->setValue(AttributesConverter::convertAnyValue($value));
        }
        if (method_exists($pElement, 'setDroppedAttributesCount')) {
            $pElement->setDroppedAttributesCount($attributes->getDroppedAttributesCount());
        }
    }
}
