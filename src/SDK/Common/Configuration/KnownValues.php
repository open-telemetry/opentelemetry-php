<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration;

use Psr\Log\LogLevel;

/**
 * "Known values" for OpenTelemetry configurataion variables.
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md
 * Notice: Values specific to the PHP SDK have been added
 */
interface KnownValues
{
    public const VALUE_TRUE = 'true';
    public const VALUE_FALSE = 'false';
    public const VALUE_ON = 'on';
    public const VALUE_OFF = 'off';
    public const VALUE_1 = '1';
    public const VALUE_0 = '0';
    public const VALUE_ALL = 'all';
    public const VALUE_NONE = 'none';
    public const VALUE_TRACECONTEXT = 'tracecontext';
    public const VALUE_BAGGAGE = 'baggage';
    public const VALUE_B3 = 'b3';
    public const VALUE_B3_MULTI = 'b3multi';
    public const VALUE_CLOUD_TRACE = 'cloudtrace';
    public const VALUE_CLOUD_TRACE_ONEWAY = 'cloudtrace-oneway';
    public const VALUE_JAEGER = 'jaeger';
    public const VALUE_JAEGER_BAGGAGE = 'jaeger-baggage';
    public const VALUE_XRAY = 'xray';
    public const VALUE_OTTRACE = 'ottrace';
    public const VALUE_ALWAYS_ON = 'always_on';
    public const VALUE_ALWAYS_OFF = 'always_off';
    public const VALUE_TRACE_ID_RATIO = 'traceidratio';
    public const VALUE_PARENT_BASED_ALWAYS_ON = 'parentbased_always_on';
    public const VALUE_PARENT_BASED_ALWAYS_OFF = 'parentbased_always_off';
    public const VALUE_PARENT_BASED_TRACE_ID_RATIO = 'parentbased_traceidratio';
    public const VALUE_GZIP = 'gzip';
    public const VALUE_GRPC = 'grpc';
    public const VALUE_HTTP_PROTOBUF = 'http/protobuf';
    public const VALUE_HTTP_JSON = 'http/json';
    public const VALUE_HTTP_NDJSON = 'http/ndjson';
    public const VALUE_OTLP = 'otlp';
    public const VALUE_OTLP_STDOUT = 'otlp/stdout';
    public const VALUE_ZIPKIN = 'zipkin';
    public const VALUE_PROMETHEUS = 'prometheus';
    public const VALUE_WITH_SAMPLED_TRACE = 'with_sampled_trace';
    public const VALUE_BATCH = 'batch';
    public const VALUE_SIMPLE = 'simple';
    public const VALUE_NOOP = 'noop';
    public const VALUE_LOG_EMERGENCY = LogLevel::EMERGENCY;
    public const VALUE_LOG_ALERT = LogLevel::ALERT;
    public const VALUE_LOG_CRITICAL = LogLevel::CRITICAL;
    public const VALUE_LOG_ERROR = LogLevel::ERROR;
    public const VALUE_LOG_WARNING = LogLevel::WARNING;
    public const VALUE_LOG_NOTICE = LogLevel::NOTICE;
    public const VALUE_LOG_INFO = LogLevel::INFO;
    public const VALUE_LOG_DEBUG = LogLevel::DEBUG;
    public const VALUE_TEMPORALITY_CUMULATIVE = 'cumulative';
    public const VALUE_TEMPORALITY_DELTA = 'delta';
    public const VALUE_TEMPORALITY_LOW_MEMORY = 'lowmemory';
    public const VALUE_HISTOGRAM_AGGREGATION_EXPLICIT = 'explicit_bucket_histogram';
    public const VALUE_HISTOGRAM_AGGREGATION_BASE2_EXPONENTIAL = 'base2_exponential_bucket_histogram';

    public const VALUES_BOOLEAN = [
        self::VALUE_TRUE,
        self::VALUE_FALSE,
    ];

    public const VALUES_COMPRESSION= [
        self::VALUE_GZIP,
        self::VALUE_NONE,
    ];

    public const VALUES_OTLP_PROTOCOL = [
        self::VALUE_GRPC,
        self::VALUE_HTTP_PROTOBUF,
        self::VALUE_HTTP_JSON,
    ];

    public const VALUES_TEMPORALITY_PREFERENCE = [
        self::VALUE_TEMPORALITY_CUMULATIVE,
        self::VALUE_TEMPORALITY_DELTA,
        self::VALUE_TEMPORALITY_LOW_MEMORY,
    ];

    public const VALUES_HISTOGRAM_AGGREGATION = [
        self::VALUE_HISTOGRAM_AGGREGATION_EXPLICIT,
        self::VALUE_HISTOGRAM_AGGREGATION_BASE2_EXPONENTIAL,
    ];

    /**
     * General SDK Configuration
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#general-sdk-configuration
     */
    public const OTEL_LOG_LEVEL = [
        self::VALUE_LOG_EMERGENCY,
        self::VALUE_LOG_ALERT,
        self::VALUE_LOG_CRITICAL,
        self::VALUE_LOG_ERROR,
        self::VALUE_LOG_WARNING,
        self::VALUE_LOG_NOTICE,
        self::VALUE_LOG_INFO,
        self::VALUE_LOG_DEBUG,
    ];
    public const OTEL_PROPAGATORS = [
        self::VALUE_TRACECONTEXT, // W3C Trace Context
        self::VALUE_BAGGAGE, // W3C Baggage
        self::VALUE_B3, // B3 Single
        self::VALUE_B3_MULTI, // B3 Multi
        self::VALUE_CLOUD_TRACE, // GCP XCloudTraceContext
        self::VALUE_CLOUD_TRACE_ONEWAY, // GCP XCloudTraceContext OneWay (Extract)
        self::VALUE_JAEGER, // Jaeger Propagator
        self::VALUE_JAEGER_BAGGAGE, // Jaeger Baggage Propagator
        self::VALUE_XRAY, // AWS X-Ray (third party)
        self::VALUE_OTTRACE, // OT Trace (third party)
        self::VALUE_NONE, // No automatically configured propagator.
    ];
    public const OTEL_TRACES_SAMPLER = [
        self::VALUE_ALWAYS_ON,
        self::VALUE_ALWAYS_OFF,
        self::VALUE_TRACE_ID_RATIO,
        self::VALUE_PARENT_BASED_ALWAYS_ON,
        self::VALUE_PARENT_BASED_ALWAYS_OFF,
        self::VALUE_PARENT_BASED_TRACE_ID_RATIO,
        self::VALUE_XRAY,
    ];
    /**
     * OTLP Exporter
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/protocol/exporter.md#configuration-options
     */
    // Insecure
    public const OTEL_EXPORTER_OTLP_INSECURE  = self::VALUES_BOOLEAN;
    public const OTEL_EXPORTER_OTLP_TRACES_INSECURE = self::VALUES_BOOLEAN;
    public const OTEL_EXPORTER_OTLP_METRICS_INSECURE  = self::VALUES_BOOLEAN;
    // Compression
    public const OTEL_EXPORTER_OTLP_COMPRESSION = self::VALUES_COMPRESSION;
    public const OTEL_EXPORTER_OTLP_TRACES_COMPRESSION = self::VALUES_COMPRESSION;
    public const OTEL_EXPORTER_OTLP_METRICS_COMPRESSION = self::VALUES_COMPRESSION;
    // Protocol
    public const OTEL_EXPORTER_OTLP_PROTOCOL = self::VALUES_OTLP_PROTOCOL;
    public const OTEL_EXPORTER_OTLP_TRACES_PROTOCOL = self::VALUES_OTLP_PROTOCOL;
    public const OTEL_EXPORTER_OTLP_METRICS_PROTOCOL = self::VALUES_OTLP_PROTOCOL;
    /**
     * Exporter Selection
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#exporter-selection
     */
    public const OTEL_TRACES_EXPORTER = [
        self::VALUE_OTLP,
        self::VALUE_OTLP_STDOUT,
        self::VALUE_ZIPKIN,
        self::VALUE_NONE,
    ];
    public const OTEL_METRICS_EXPORTER = [
        self::VALUE_OTLP,
        self::VALUE_OTLP_STDOUT,
        self::VALUE_PROMETHEUS,
        self::VALUE_NONE,
    ];
    public const OTEL_LOGS_EXPORTER = [
        self::VALUE_OTLP,
        self::VALUE_OTLP_STDOUT,
        self::VALUE_NONE,
    ];
    /**
     * Metrics SDK Configuration
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#metrics-sdk-configuration
     */
    public const OTEL_METRICS_EXEMPLAR_FILTER = [
        self::VALUE_WITH_SAMPLED_TRACE,
        self::VALUE_ALL,
        self::VALUE_NONE,
    ];
    /**
     * Language Specific Environment Variables
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/sdk-environment-variables.md#language-specific-environment-variables
     */
    public const OTEL_PHP_TRACES_PROCESSOR = [
        self::VALUE_BATCH,
        self::VALUE_SIMPLE,
        self::VALUE_NOOP,
        self::VALUE_NONE,
    ];
    public const OTEL_PHP_AUTOLOAD_ENABLED = self::VALUES_BOOLEAN;
    public const VALUE_ERROR_LOG = 'error_log';
    public const VALUE_STDERR = 'stderr';
    public const VALUE_STDOUT = 'stdout';
    public const VALUE_PSR3 = 'psr3';
    public const VALUE_EMPTY = '';
    public const VALUE_DETECTORS_ENVIRONMENT = 'env';
    public const VALUE_DETECTORS_HOST = 'host';
    public const VALUE_DETECTORS_OS = 'os';
    public const VALUE_DETECTORS_PROCESS = 'process';
    public const VALUE_DETECTORS_PROCESS_RUNTIME = 'process_runtime';
    public const VALUE_DETECTORS_SDK = 'sdk';
    public const VALUE_DETECTORS_SDK_PROVIDED = 'sdk_provided';
    public const VALUE_DETECTORS_SERVICE = 'service';
    public const VALUE_DETECTORS_SERVICE_INSTANCE = 'service_instance';
    public const VALUE_DETECTORS_COMPOSER = 'composer';
    public const OTEL_PHP_DETECTORS = [
        self::VALUE_ALL,
        self::VALUE_DETECTORS_ENVIRONMENT,
        self::VALUE_DETECTORS_HOST,
        self::VALUE_DETECTORS_OS,
        self::VALUE_DETECTORS_PROCESS,
        self::VALUE_DETECTORS_PROCESS_RUNTIME,
        self::VALUE_DETECTORS_SDK,
        self::VALUE_DETECTORS_SDK_PROVIDED,
        self::VALUE_DETECTORS_COMPOSER,
        self::VALUE_NONE,
    ];
    public const OTEL_PHP_LOG_DESTINATION = [
        self::VALUE_ERROR_LOG,
        self::VALUE_STDERR,
        self::VALUE_STDOUT,
        self::VALUE_PSR3,
        self::VALUE_EMPTY,
        self::VALUE_NONE,
    ];

    public const OTEL_EXPERIMENTAL_RESPONSE_PROPAGATORS = [
        self::VALUE_NONE, // No automatically configured propagator.
    ];
}
