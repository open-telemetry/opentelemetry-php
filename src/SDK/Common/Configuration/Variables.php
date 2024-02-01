<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration;

/**
 * Environment variables defined by the OpenTelemetry specification and language specific variables for the PHP SDK.
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md
 */
interface Variables
{
    /**
     * General SDK Configuration
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#general-sdk-configuration
     */
    public const OTEL_RESOURCE_ATTRIBUTES = 'OTEL_RESOURCE_ATTRIBUTES';
    public const OTEL_SERVICE_NAME = 'OTEL_SERVICE_NAME';
    public const OTEL_LOG_LEVEL = 'OTEL_LOG_LEVEL';
    public const OTEL_PROPAGATORS = 'OTEL_PROPAGATORS';
    public const OTEL_TRACES_SAMPLER = 'OTEL_TRACES_SAMPLER';
    public const OTEL_TRACES_SAMPLER_ARG = 'OTEL_TRACES_SAMPLER_ARG';
    public const OTEL_SDK_DISABLED = 'OTEL_SDK_DISABLED';
    /**
     * Batch Span Processor
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#batch-span-processor
     */
    public const OTEL_BSP_SCHEDULE_DELAY = 'OTEL_BSP_SCHEDULE_DELAY';
    public const OTEL_BSP_EXPORT_TIMEOUT = 'OTEL_BSP_EXPORT_TIMEOUT';
    public const OTEL_BSP_MAX_QUEUE_SIZE = 'OTEL_BSP_MAX_QUEUE_SIZE';
    public const OTEL_BSP_MAX_EXPORT_BATCH_SIZE = 'OTEL_BSP_MAX_EXPORT_BATCH_SIZE';
    /**
     * Batch LogRecord Processor
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#batch-logrecord-processor
     */
    public const OTEL_BLRP_SCHEDULE_DELAY = 'OTEL_BLRP_SCHEDULE_DELAY';
    public const OTEL_BLRP_EXPORT_TIMEOUT = 'OTEL_BLRP_EXPORT_TIMEOUT';
    public const OTEL_BLRP_MAX_QUEUE_SIZE = 'OTEL_BLRP_MAX_QUEUE_SIZE';
    public const OTEL_BLRP_MAX_EXPORT_BATCH_SIZE = 'OTEL_BLRP_MAX_EXPORT_BATCH_SIZE';
    /**
     * Attribute Limits
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#attribute-limits
     */
    public const OTEL_ATTRIBUTE_VALUE_LENGTH_LIMIT = 'OTEL_ATTRIBUTE_VALUE_LENGTH_LIMIT';
    public const OTEL_ATTRIBUTE_COUNT_LIMIT = 'OTEL_ATTRIBUTE_COUNT_LIMIT';
    /**
     * LogRecord limits
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#logrecord-limits
     */
    public const OTEL_LOGRECORD_ATTRIBUTE_VALUE_LENGTH_LIMIT = 'OTEL_LOGRECORD_ATTRIBUTE_VALUE_LENGTH_LIMIT';
    public const OTEL_LOGRECORD_ATTRIBUTE_COUNT_LIMIT = 'OTEL_LOGRECORD_ATTRIBUTE_COUNT_LIMIT';
    /**
     * Span Limits
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#span-limits
     */
    public const OTEL_SPAN_ATTRIBUTE_VALUE_LENGTH_LIMIT = 'OTEL_SPAN_ATTRIBUTE_VALUE_LENGTH_LIMIT';
    public const OTEL_SPAN_ATTRIBUTE_COUNT_LIMIT = 'OTEL_SPAN_ATTRIBUTE_COUNT_LIMIT';
    public const OTEL_SPAN_EVENT_COUNT_LIMIT = 'OTEL_SPAN_EVENT_COUNT_LIMIT';
    public const OTEL_SPAN_LINK_COUNT_LIMIT = 'OTEL_SPAN_LINK_COUNT_LIMIT';
    public const OTEL_EVENT_ATTRIBUTE_COUNT_LIMIT = 'OTEL_EVENT_ATTRIBUTE_COUNT_LIMIT';
    public const OTEL_LINK_ATTRIBUTE_COUNT_LIMIT = 'OTEL_LINK_ATTRIBUTE_COUNT_LIMIT';
    /**
     * OTLP Exporter
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/protocol/exporter.md#configuration-options
     */
    // Endpoint
    public const OTEL_EXPORTER_OTLP_ENDPOINT = 'OTEL_EXPORTER_OTLP_ENDPOINT';
    public const OTEL_EXPORTER_OTLP_TRACES_ENDPOINT = 'OTEL_EXPORTER_OTLP_TRACES_ENDPOINT';
    public const OTEL_EXPORTER_OTLP_METRICS_ENDPOINT = 'OTEL_EXPORTER_OTLP_METRICS_ENDPOINT';
    public const OTEL_EXPORTER_OTLP_LOGS_ENDPOINT = 'OTEL_EXPORTER_OTLP_LOGS_ENDPOINT';
    // Insecure
    public const OTEL_EXPORTER_OTLP_INSECURE = 'OTEL_EXPORTER_OTLP_INSECURE';
    public const OTEL_EXPORTER_OTLP_TRACES_INSECURE = 'OTEL_EXPORTER_OTLP_TRACES_INSECURE';
    public const OTEL_EXPORTER_OTLP_METRICS_INSECURE = 'OTEL_EXPORTER_OTLP_METRICS_INSECURE';
    public const OTEL_EXPORTER_OTLP_LOGS_INSECURE = 'OTEL_EXPORTER_OTLP_LOGS_INSECURE';
    // Certificate File
    public const OTEL_EXPORTER_OTLP_CERTIFICATE = 'OTEL_EXPORTER_OTLP_CERTIFICATE';
    public const OTEL_EXPORTER_OTLP_TRACES_CERTIFICATE = 'OTEL_EXPORTER_OTLP_TRACES_CERTIFICATE';
    public const OTEL_EXPORTER_OTLP_METRICS_CERTIFICATE = 'OTEL_EXPORTER_OTLP_METRICS_CERTIFICATE';
    public const OTEL_EXPORTER_OTLP_LOGS_CERTIFICATE = 'OTEL_EXPORTER_OTLP_LOGS_CERTIFICATE';
    // Headers
    public const OTEL_EXPORTER_OTLP_HEADERS = 'OTEL_EXPORTER_OTLP_HEADERS';
    public const OTEL_EXPORTER_OTLP_TRACES_HEADERS = 'OTEL_EXPORTER_OTLP_TRACES_HEADERS';
    public const OTEL_EXPORTER_OTLP_METRICS_HEADERS = 'OTEL_EXPORTER_OTLP_METRICS_HEADERS';
    public const OTEL_EXPORTER_OTLP_LOGS_HEADERS = 'OTEL_EXPORTER_OTLP_LOGS_HEADERS';
    // Compression
    public const OTEL_EXPORTER_OTLP_COMPRESSION = 'OTEL_EXPORTER_OTLP_COMPRESSION';
    public const OTEL_EXPORTER_OTLP_TRACES_COMPRESSION = 'OTEL_EXPORTER_OTLP_TRACES_COMPRESSION';
    public const OTEL_EXPORTER_OTLP_METRICS_COMPRESSION = 'OTEL_EXPORTER_OTLP_METRICS_COMPRESSION';
    public const OTEL_EXPORTER_OTLP_LOGS_COMPRESSION = 'OTEL_EXPORTER_OTLP_LOGS_COMPRESSION';
    // Timeout
    public const OTEL_EXPORTER_OTLP_TIMEOUT = 'OTEL_EXPORTER_OTLP_TIMEOUT';
    public const OTEL_EXPORTER_OTLP_TRACES_TIMEOUT = 'OTEL_EXPORTER_OTLP_TRACES_TIMEOUT';
    public const OTEL_EXPORTER_OTLP_METRICS_TIMEOUT = 'OTEL_EXPORTER_OTLP_METRICS_TIMEOUT';
    public const OTEL_EXPORTER_OTLP_LOGS_TIMEOUT = 'OTEL_EXPORTER_OTLP_LOGS_TIMEOUT';
    // Protocol
    public const OTEL_EXPORTER_OTLP_PROTOCOL = 'OTEL_EXPORTER_OTLP_PROTOCOL';
    public const OTEL_EXPORTER_OTLP_TRACES_PROTOCOL = 'OTEL_EXPORTER_OTLP_TRACES_PROTOCOL';
    public const OTEL_EXPORTER_OTLP_METRICS_PROTOCOL = 'OTEL_EXPORTER_OTLP_METRICS_PROTOCOL';
    public const OTEL_EXPORTER_OTLP_LOGS_PROTOCOL = 'OTEL_EXPORTER_OTLP_LOGS_PROTOCOL';
    /**
     * Zipkin Exporter
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#zipkin-exporter
     */
    public const OTEL_EXPORTER_ZIPKIN_ENDPOINT = 'OTEL_EXPORTER_ZIPKIN_ENDPOINT';
    public const OTEL_EXPORTER_ZIPKIN_TIMEOUT = 'OTEL_EXPORTER_ZIPKIN_TIMEOUT';
    public const OTEL_EXPORTER_ZIPKIN_PROTOCOL = 'OTEL_EXPORTER_ZIPKIN_PROTOCOL';
    /**
     * Prometheus Exporter
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#prometheus-exporter
     */
    public const OTEL_EXPORTER_PROMETHEUS_HOST = 'OTEL_EXPORTER_PROMETHEUS_HOST';
    public const OTEL_EXPORTER_PROMETHEUS_PORT = 'OTEL_EXPORTER_PROMETHEUS_PORT';
    /**
     * Exporter Selection
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#exporter-selection
     */
    public const OTEL_TRACES_EXPORTER = 'OTEL_TRACES_EXPORTER';
    public const OTEL_METRICS_EXPORTER = 'OTEL_METRICS_EXPORTER';
    public const OTEL_LOGS_EXPORTER = 'OTEL_LOGS_EXPORTER';
    /**
     * Metrics SDK Configuration
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#metrics-sdk-configuration
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#periodic-exporting-metricreader
     */
    public const OTEL_METRICS_EXEMPLAR_FILTER = 'OTEL_METRICS_EXEMPLAR_FILTER';
    public const OTEL_METRIC_EXPORT_INTERVAL = 'OTEL_METRIC_EXPORT_INTERVAL';
    public const OTEL_METRIC_EXPORT_TIMEOUT = 'OTEL_METRIC_EXPORT_TIMEOUT';
    public const OTEL_EXPORTER_OTLP_METRICS_TEMPORALITY_PREFERENCE = 'OTEL_EXPORTER_OTLP_METRICS_TEMPORALITY_PREFERENCE';
    public const OTEL_EXPORTER_OTLP_METRICS_DEFAULT_HISTOGRAM_AGGREGATION = 'OTEL_EXPORTER_OTLP_METRICS_DEFAULT_HISTOGRAM_AGGREGATION';
    /**
     * Language Specific Environment Variables
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#language-specific-environment-variables
     */
    public const OTEL_PHP_TRACES_PROCESSOR = 'OTEL_PHP_TRACES_PROCESSOR';
    public const OTEL_PHP_LOGS_PROCESSOR = 'OTEL_PHP_LOGS_PROCESSOR';
    public const OTEL_PHP_LOG_DESTINATION = 'OTEL_PHP_LOG_DESTINATION';
    public const OTEL_PHP_DETECTORS = 'OTEL_PHP_DETECTORS';
    public const OTEL_PHP_AUTOLOAD_ENABLED = 'OTEL_PHP_AUTOLOAD_ENABLED';
    public const OTEL_PHP_INTERNAL_METRICS_ENABLED = 'OTEL_PHP_INTERNAL_METRICS_ENABLED'; //whether the SDK should emit its own metrics
    public const OTEL_PHP_DISABLED_INSTRUMENTATIONS = 'OTEL_PHP_DISABLED_INSTRUMENTATIONS';
    public const OTEL_PHP_EXCLUDED_URLS = 'OTEL_PHP_EXCLUDED_URLS';
}
