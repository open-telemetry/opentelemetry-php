<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration;

/**
 * Default values for environment variables defined by the OpenTelemetry specification and language specific variables for the PHP SDK.
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md
 */
interface Defaults
{
    /**
     * General SDK Configuration
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#general-sdk-configuration
     */
    public const OTEL_LOG_LEVEL = 'info';
    public const OTEL_PROPAGATORS = 'tracecontext,baggage';
    public const OTEL_TRACES_SAMPLER = 'parentbased_always_on';
    public const OTEL_SDK_DISABLED = 'false';
    /**
     * Batch Span Processor
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#batch-span-processor
     */
    public const OTEL_BSP_SCHEDULE_DELAY = 5000;
    public const OTEL_BSP_EXPORT_TIMEOUT = 30000;
    public const OTEL_BSP_MAX_QUEUE_SIZE = 2048;
    public const OTEL_BSP_MAX_EXPORT_BATCH_SIZE = 512;
    /**
     * Attribute Limits
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#attribute-limits
     */
    public const OTEL_ATTRIBUTE_COUNT_LIMIT = 128;
    /**
     * Span Limits
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#span-limits-
     */
    public const OTEL_SPAN_ATTRIBUTE_COUNT_LIMIT = 128;
    public const OTEL_SPAN_EVENT_COUNT_LIMIT = 128;
    public const OTEL_SPAN_LINK_COUNT_LIMIT = 128;
    public const OTEL_EVENT_ATTRIBUTE_COUNT_LIMIT = 128;
    public const OTEL_LINK_ATTRIBUTE_COUNT_LIMIT = 128;
    /**
     * OTLP Exporter
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/protocol/exporter.md#configuration-options
     */
    // Endpoint
    public const OTEL_EXPORTER_OTLP_ENDPOINT = 'http://localhost:4318';
    public const OTEL_EXPORTER_OTLP_TRACES_ENDPOINT = 'http://localhost:4318';
    public const OTEL_EXPORTER_OTLP_METRICS_ENDPOINT = 'http://localhost:4318';
    // Insecure
    public const OTEL_EXPORTER_OTLP_INSECURE = 'false';
    public const OTEL_EXPORTER_OTLP_TRACES_INSECURE = 'false';
    public const OTEL_EXPORTER_OTLP_METRICS_INSECURE = 'false';
    // Timeout (seconds)
    public const OTEL_EXPORTER_OTLP_TIMEOUT = 10;
    public const OTEL_EXPORTER_OTLP_TRACES_TIMEOUT = 10;
    public const OTEL_EXPORTER_OTLP_METRICS_TIMEOUT = 10;
    // Protocol
    public const OTEL_EXPORTER_OTLP_PROTOCOL = 'http/protobuf';
    public const OTEL_EXPORTER_OTLP_TRACES_PROTOCOL = 'http/protobuf';
    public const OTEL_EXPORTER_OTLP_METRICS_PROTOCOL = 'http/protobuf';
    /**
     * Jaeger Exporter
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#jaeger-exporter
     */
    public const OTEL_EXPORTER_JAEGER_AGENT_HOST = 'localhost';
    public const OTEL_EXPORTER_JAEGER_AGENT_PORT = 6831;
    public const OTEL_EXPORTER_JAEGER_ENDPOINT = 'http://localhost:14250';
    // Timeout (seconds)
    public const OTEL_EXPORTER_JAEGER_TIMEOUT = 10;
    /**
     * Zipkin Exporter
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#zipkin-exporter
     */
    public const OTEL_EXPORTER_ZIPKIN_ENDPOINT = 'http://localhost:9411/api/v2/spans';
    // Timeout (seconds)
    public const OTEL_EXPORTER_ZIPKIN_TIMEOUT = 10;
    /**
     * Prometheus Exporter
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#prometheus-exporter
     */
    public const OTEL_EXPORTER_PROMETHEUS_HOST = '0.0.0.0';
    public const OTEL_EXPORTER_PROMETHEUS_PORT = 9464;
    /**
     * Exporter Selection
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#exporter-selection
     */
    public const OTEL_TRACES_EXPORTER = 'otlp';
    public const OTEL_METRICS_EXPORTER = 'otlp';
    public const OTEL_LOGS_EXPORTER = 'otlp';
    /**
     * Metrics SDK Configuration
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#metrics-sdk-configuration
     */
    public const OTEL_METRICS_EXEMPLAR_FILTER = 'with_sampled_trace';
    public const OTEL_METRIC_EXPORT_INTERVAL = 60000;
    public const OTEL_METRIC_EXPORT_TIMEOUT = 30000;
    /**
     * Language Specific Environment Variables
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#language-specific-environment-variables
     */
    public const OTEL_PHP_TRACES_PROCESSOR = 'batch';
    public const OTEL_PHP_DETECTORS = 'all';
    public const OTEL_PHP_AUTOLOAD_ENABLED = 'false';
}
