<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration;

/**
 * Environment variables defined by the OpenTelemetry specification and language specific variables for the PHP SDK.
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md
 */
interface ValueTypes
{
    /**
     * General SDK Configuration
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#general-sdk-configuration
     */
    public const OTEL_RESOURCE_ATTRIBUTES = VariableTypes::MAP;
    public const OTEL_SERVICE_NAME = VariableTypes::STRING;
    public const OTEL_LOG_LEVEL = VariableTypes::ENUM;
    public const OTEL_PROPAGATORS = VariableTypes::LIST;
    public const OTEL_TRACES_SAMPLER = VariableTypes::STRING;
    public const OTEL_TRACES_SAMPLER_ARG = VariableTypes::MIXED;
    /**
     * Batch Span Processor
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#batch-span-processor
     */
    public const OTEL_BSP_SCHEDULE_DELAY = VariableTypes::INTEGER;
    public const OTEL_BSP_EXPORT_TIMEOUT = VariableTypes::INTEGER;
    public const OTEL_BSP_MAX_QUEUE_SIZE = VariableTypes::INTEGER;
    public const OTEL_BSP_MAX_EXPORT_BATCH_SIZE = VariableTypes::INTEGER;
    /**
     * Batch LogRecord Processor
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#batch-logrecord-processor
     */
    public const OTEL_BLRP_SCHEDULE_DELAY = VariableTypes::INTEGER;
    public const OTEL_BLRP_EXPORT_TIMEOUT = VariableTypes::INTEGER;
    public const OTEL_BLRP_MAX_QUEUE_SIZE = VariableTypes::INTEGER;
    public const OTEL_BLRP_MAX_EXPORT_BATCH_SIZE = VariableTypes::INTEGER;
    /**
     * Attribute Limits
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#attribute-limits
     */
    public const OTEL_ATTRIBUTE_VALUE_LENGTH_LIMIT = VariableTypes::INTEGER;
    public const OTEL_ATTRIBUTE_COUNT_LIMIT = VariableTypes::INTEGER;
    /**
     * Span Limits
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#span-limits
     */
    public const OTEL_SPAN_ATTRIBUTE_VALUE_LENGTH_LIMIT = VariableTypes::INTEGER;
    public const OTEL_SPAN_ATTRIBUTE_COUNT_LIMIT = VariableTypes::INTEGER;
    public const OTEL_SPAN_EVENT_COUNT_LIMIT = VariableTypes::INTEGER;
    public const OTEL_SPAN_LINK_COUNT_LIMIT = VariableTypes::INTEGER;
    public const OTEL_EVENT_ATTRIBUTE_COUNT_LIMIT = VariableTypes::INTEGER;
    public const OTEL_LINK_ATTRIBUTE_COUNT_LIMIT = VariableTypes::INTEGER;
    /**
     * LogRecord Limits
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#logrecord-limits
     */
    public const OTEL_LOGRECORD_ATTRIBUTE_VALUE_LENGTH_LIMIT = VariableTypes::INTEGER;
    public const OTEL_LOGRECORD_ATTRIBUTE_COUNT_LIMIT = VariableTypes::INTEGER;
    /**
     * OTLP Exporter
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/protocol/exporter.md#configuration-options
     */
    // Endpoint
    public const OTEL_EXPORTER_OTLP_ENDPOINT = VariableTypes::STRING;
    public const OTEL_EXPORTER_OTLP_TRACES_ENDPOINT = VariableTypes::STRING;
    public const OTEL_EXPORTER_OTLP_METRICS_ENDPOINT = VariableTypes::STRING;
    // Insecure
    public const OTEL_EXPORTER_OTLP_INSECURE = VariableTypes::BOOL;
    public const OTEL_EXPORTER_OTLP_TRACES_INSECURE = VariableTypes::BOOL;
    public const OTEL_EXPORTER_OTLP_METRICS_INSECURE = VariableTypes::BOOL;
    // Certificate File
    public const OTEL_EXPORTER_OTLP_CERTIFICATE = VariableTypes::STRING;
    public const OTEL_EXPORTER_OTLP_TRACES_CERTIFICATE = VariableTypes::STRING;
    public const OTEL_EXPORTER_OTLP_METRICS_CERTIFICATE = VariableTypes::STRING;
    // Headers
    public const OTEL_EXPORTER_OTLP_HEADERS = VariableTypes::MAP;
    public const OTEL_EXPORTER_OTLP_TRACES_HEADERS = VariableTypes::MAP;
    public const OTEL_EXPORTER_OTLP_METRICS_HEADERS = VariableTypes::MAP;
    // Compression
    public const OTEL_EXPORTER_OTLP_COMPRESSION = VariableTypes::ENUM;
    public const OTEL_EXPORTER_OTLP_TRACES_COMPRESSION = VariableTypes::ENUM;
    public const OTEL_EXPORTER_OTLP_METRICS_COMPRESSION = VariableTypes::ENUM;
    // Timeout
    public const OTEL_EXPORTER_OTLP_TIMEOUT = VariableTypes::INTEGER;
    public const OTEL_EXPORTER_OTLP_TRACES_TIMEOUT = VariableTypes::INTEGER;
    public const OTEL_EXPORTER_OTLP_METRICS_TIMEOUT = VariableTypes::INTEGER;
    // Protocol
    public const OTEL_EXPORTER_OTLP_PROTOCOL = VariableTypes::ENUM;
    public const OTEL_EXPORTER_OTLP_TRACES_PROTOCOL = VariableTypes::ENUM;
    public const OTEL_EXPORTER_OTLP_METRICS_PROTOCOL = VariableTypes::ENUM;
    /**
     * Zipkin Exporter
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#zipkin-exporter
     */
    public const OTEL_EXPORTER_ZIPKIN_ENDPOINT = VariableTypes::STRING;
    public const OTEL_EXPORTER_ZIPKIN_TIMEOUT = VariableTypes::INTEGER;
    public const OTEL_EXPORTER_ZIPKIN_PROTOCOL = VariableTypes::STRING;
    /**
     * Prometheus Exporter
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#prometheus-exporter
     */
    public const OTEL_EXPORTER_PROMETHEUS_HOST = VariableTypes::STRING;
    public const OTEL_EXPORTER_PROMETHEUS_PORT = VariableTypes::INTEGER;
    /**
     * Exporter Selection
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#exporter-selection
     */
    public const OTEL_TRACES_EXPORTER = VariableTypes::LIST;
    public const OTEL_METRICS_EXPORTER = VariableTypes::LIST;
    public const OTEL_LOGS_EXPORTER = VariableTypes::LIST;
    /**
     * Metrics SDK Configuration
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#metrics-sdk-configuration
     */
    public const OTEL_METRICS_EXEMPLAR_FILTER = VariableTypes::ENUM;
    public const OTEL_METRIC_EXPORT_INTERVAL = VariableTypes::INTEGER;
    public const OTEL_METRIC_EXPORT_TIMEOUT = VariableTypes::INTEGER;
    public const OTEL_EXPORTER_OTLP_METRICS_TEMPORALITY_PREFERENCE = VariableTypes::ENUM;
    public const OTEL_EXPORTER_OTLP_METRICS_DEFAULT_HISTOGRAM_AGGREGATION = VariableTypes::ENUM;
    /**
     * Language Specific Environment Variables
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#language-specific-environment-variables
     */
    public const OTEL_PHP_TRACES_PROCESSOR = VariableTypes::ENUM;
    public const OTEL_PHP_LOGS_PROCESSOR = VariableTypes::LIST;
    public const OTEL_PHP_DETECTORS = VariableTypes::LIST;
    public const OTEL_PHP_AUTOLOAD_ENABLED = VariableTypes::BOOL;
    public const OTEL_PHP_LOG_DESTINATION = VariableTypes::ENUM;
    public const OTEL_PHP_INTERNAL_METRICS_ENABLED = VariableTypes::BOOL;
    public const OTEL_PHP_DISABLED_INSTRUMENTATIONS = VariableTypes::LIST;
}
