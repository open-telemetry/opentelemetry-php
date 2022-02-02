<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Environment;

use Psr\Log\LogLevel;

/**
 * "Known values" for OpenTelemetry environment variables.
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md
 * Notice: Values specific to the PHP SDK have been added
 */
interface KnownValues
{
    /**
     * General SDK Configuration
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#general-sdk-configuration
     */
    public const OTEL_LOG_LEVEL = [
        LogLevel::EMERGENCY,
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::ERROR,
        LogLevel::WARNING,
        LogLevel::NOTICE,
        LogLevel::INFO,
        LogLevel::DEBUG,
    ];
    public const OTEL_PROPAGATORS = [
        'tracecontext', // W3C Trace Context
        'baggage', // W3C Baggage
        'b3', // B3 Single
        'b3multi', // B3 Multi
        'xray', // AWS X-Ray (third party)
        'ottrace', // OT Trace (third party)
        'none', // No automatically configured propagator.
    ];
    public const OTEL_TRACES_SAMPLER = [
        'always_on',
        'always_off',
        'traceidratio',
        'parentbased_always_on',
        'parentbased_always_off',
        'parentbased_traceidratio',
        'jaeger_remote',
        'xray',
    ];
    /**
     * OTLP Exporter
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/protocol/exporter.md#configuration-options
     */
    // Insecure
    public const OTEL_EXPORTER_OTLP_INSECURE = [
        'true',
        'false',
        'on',
        'off',
        1,
        0,
    ];
    public const OTEL_EXPORTER_OTLP_SPAN_INSECURE = [
        'true',
        'false',
        'on',
        'off',
        1,
        0,
    ];
    public const OTEL_EXPORTER_OTLP_METRIC_INSECURE = [
        'true',
        'false',
        'on',
        'off',
        1,
        0,
    ];
    // Compression
    public const OTEL_EXPORTER_OTLP_COMPRESSION = ['gzip', 'none'];
    public const OTEL_EXPORTER_OTLP_TRACES_COMPRESSION = ['gzip', 'none'];
    public const OTEL_EXPORTER_OTLP_METRICS_COMPRESSION = ['gzip', 'none'];
    // Protocol
    public const OTEL_EXPORTER_OTLP_PROTOCOL = [
        'grpc',
        'http/protobuf',
        'http/json',
    ];
    public const OTEL_EXPORTER_OTLP_TRACES_PROTOCOL = [
        'grpc',
        'http/protobuf',
        'http/json',
    ];
    public const OTEL_EXPORTER_OTLP_METRICS_PROTOCOL = [
        'grpc',
        'http/protobuf',
        'http/json',
    ];
    /**
     * Exporter Selection
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#exporter-selection
     */
    public const OTEL_TRACES_EXPORTER = [
        'otlp',
        'jaeger',
        'zipkin',
        'newrelic',
        'none',
    ];
    public const OTEL_METRICS_EXPORTER = [
        'otlp',
        'prometheus',
        'none',
    ];
    public const OTEL_LOGS_EXPORTER = [
        'otlp',
        'none',
    ];
    /**
     * Metrics SDK Configuration
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#metrics-sdk-configuration
     */
    public const OTEL_METRICS_EXEMPLAR_FILTER = [
        'with_sampled_trace',
        'all',
        'none',
    ];
    /**
     * Language Specific Environment Variables
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#language-specific-environment-variables
     */
    public const OTEL_PHP_TRACES_PROCESSOR = [
        'batch',
        'simple',
        'noop',
        'none',
    ];
}
