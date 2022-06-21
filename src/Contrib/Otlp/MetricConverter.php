<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\API\Metrics\CounterInterface;
use OpenTelemetry\API\Metrics\LabelableMetricInterfaceInterface;
use OpenTelemetry\API\Metrics\MetricInterface;
use Opentelemetry\Proto\Common\V1\AnyValue;
use Opentelemetry\Proto\Common\V1\ArrayValue;
use Opentelemetry\Proto\Common\V1\InstrumentationScope;
use Opentelemetry\Proto\Common\V1\KeyValue;
use Opentelemetry\Proto\Metrics\V1\AggregationTemporality;
use Opentelemetry\Proto\Metrics\V1\Metric;
use Opentelemetry\Proto\Metrics\V1\NumberDataPoint;
use Opentelemetry\Proto\Metrics\V1\ResourceMetrics;
use Opentelemetry\Proto\Metrics\V1\ScopeMetrics;
use Opentelemetry\Proto\Metrics\V1\Sum;
use Opentelemetry\Proto\Resource\V1\Resource;
use OpenTelemetry\SDK\Common\Instrumentation\KeyGenerator;
use OpenTelemetry\SDK\Metrics\Exceptions\CantBeExported;
use OpenTelemetry\SDK\Metrics\MetricConverterInterface;

class MetricConverter implements MetricConverterInterface
{
    public function convert(iterable $metrics): array
    {
        return [$this->as_otlp_resource_metric($metrics)];
    }

    private function as_otlp_resource_metric(iterable $metrics): ResourceMetrics
    {
        $instrumentationScopes = $convertedMetrics = $schemas = [];
        foreach ($metrics as /** @var MetricInterface $metric */  $metric) {
            $scope = $metric->getInstrumentationScope();
            $isKey = KeyGenerator::generateInstanceKey($scope->getName(), $scope->getVersion(), $scope->getSchemaUrl());
            if (!isset($instrumentationScopes[$isKey])) {
                $convertedMetrics[$isKey] = [];
                $instrumentationScopes[$isKey] = new InstrumentationScope(['name' => $scope->getName(), 'version' => $scope->getVersion() ?? '']);
                $schemas[$isKey] = $scope->getSchemaUrl();
            }
            $convertedMetrics[$isKey][] = $this->as_otlp_metric($metric);
        }

        if (empty($convertedMetrics)) {
            return new ResourceMetrics();
        }

        $isMetrics = [];
        foreach ($instrumentationScopes as $isKey => $scope) {
            $isMetrics[] = new ScopeMetrics([
                'scope' => $scope,
                'metrics' => $convertedMetrics[$isKey],
                'schema_url' => $schemas[$isKey] ?? '',
            ]);
        }

        return new ResourceMetrics([
            'resource' => new Resource([
                'attributes' => $this->as_otlp_resource_attributes($metrics),
            ]),
            'scope_metrics' => $isMetrics,
        ]);
    }

    private function as_otlp_metric(MetricInterface $metric): Metric
    {
        $data = [
            'name' => $metric->getName(),
            'description' => $metric->getDescription(),
        ];

        switch (true) {
            case $metric instanceof CounterInterface:
                $data += [
                    'sum' => new Sum([
                        'aggregation_temporality' => AggregationTemporality::AGGREGATION_TEMPORALITY_CUMULATIVE,
                        'is_monotonic' => true,
                        'data_points' => [new NumberDataPoint([
                            'attributes' => $metric instanceof LabelableMetricInterfaceInterface
                                ? $metric->getLabels()
                                : [],
                            'as_int' => $metric->getValue(),
                            'time_unix_nano' => $metric->getStartEpochNanos(),
                            'start_time_unix_nano' => $metric->getStartEpochNanos(),
                        ])],
                    ]),
                ];

                break;
            default:
                throw new CantBeExported('Unknown metrics type: ' . get_class($metric));
        }

        return new Metric($data);
    }

    // @return KeyValue[]
    private function as_otlp_resource_attributes(iterable $metrics): array
    {
        $attrs = [];
        foreach ($metrics as $metric) {
            foreach ($metric->getResource()->getAttributes() as $k => $v) {
                $attrs[$k] = $this->as_otlp_key_value($k, $v);
            }
        }

        return array_values($attrs);
    }

    private function as_otlp_key_value($key, $value): KeyValue
    {
        return new KeyValue([
            'key' => $key,
            'value' => $this->as_otlp_any_value($value),
        ]);
    }

    private function as_otlp_any_value($value): AnyValue
    {
        $result = new AnyValue();

        switch (true) {
            case is_array($value):
                $values = [];
                foreach ($value as $element) {
                    $values[] = $this->as_otlp_any_value($element);
                }
                $result->setArrayValue(new ArrayValue(['values' => $values]));

                break;
            case is_int($value):
                $result->setIntValue($value);

                break;
            case is_bool($value):
                $result->setBoolValue($value);

                break;
            case is_double($value):
                $result->setDoubleValue($value);

                break;
            case is_string($value):
                $result->setStringValue($value);

                break;
        }

        return $result;
    }
}
