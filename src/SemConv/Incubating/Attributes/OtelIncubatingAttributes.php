<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for otel.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/otel/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface OtelIncubatingAttributes
{
    /**
     * A name uniquely identifying the instance of the OpenTelemetry component within its containing SDK instance.
     *
     * Implementations SHOULD ensure a low cardinality for this attribute, even across application or SDK restarts.
     * E.g. implementations MUST NOT use UUIDs as values for this attribute.
     *
     * Implementations MAY achieve these goals by following a `<otel.component.type>/<instance-counter>` pattern, e.g. `batching_span_processor/0`.
     * Hereby `otel.component.type` refers to the corresponding attribute value of the component.
     *
     * The value of `instance-counter` MAY be automatically assigned by the component and uniqueness within the enclosing SDK instance MUST be guaranteed.
     * For example, `<instance-counter>` MAY be implemented by using a monotonically increasing counter (starting with `0`), which is incremented every time an
     * instance of the given component type is started.
     *
     * With this implementation, for example the first Batching Span Processor would have `batching_span_processor/0`
     * as `otel.component.name`, the second one `batching_span_processor/1` and so on.
     * These values will therefore be reused in the case of an application restart.
     *
     * @experimental
     */
    public const OTEL_COMPONENT_NAME = 'otel.component.name';

    /**
     * A name identifying the type of the OpenTelemetry component.
     *
     * If none of the standardized values apply, implementations SHOULD use the language-defined name of the type.
     * E.g. for Java the fully qualified classname SHOULD be used in this case.
     *
     * @experimental
     */
    public const OTEL_COMPONENT_TYPE = 'otel.component.type';

    /**
     * The builtin SDK batching span processor
     *
     * @experimental
     */
    public const OTEL_COMPONENT_TYPE_VALUE_BATCHING_SPAN_PROCESSOR = 'batching_span_processor';

    /**
     * The builtin SDK simple span processor
     *
     * @experimental
     */
    public const OTEL_COMPONENT_TYPE_VALUE_SIMPLE_SPAN_PROCESSOR = 'simple_span_processor';

    /**
     * The builtin SDK batching log record processor
     *
     * @experimental
     */
    public const OTEL_COMPONENT_TYPE_VALUE_BATCHING_LOG_PROCESSOR = 'batching_log_processor';

    /**
     * The builtin SDK simple log record processor
     *
     * @experimental
     */
    public const OTEL_COMPONENT_TYPE_VALUE_SIMPLE_LOG_PROCESSOR = 'simple_log_processor';

    /**
     * OTLP span exporter over gRPC with protobuf serialization
     *
     * @experimental
     */
    public const OTEL_COMPONENT_TYPE_VALUE_OTLP_GRPC_SPAN_EXPORTER = 'otlp_grpc_span_exporter';

    /**
     * OTLP span exporter over HTTP with protobuf serialization
     *
     * @experimental
     */
    public const OTEL_COMPONENT_TYPE_VALUE_OTLP_HTTP_SPAN_EXPORTER = 'otlp_http_span_exporter';

    /**
     * OTLP span exporter over HTTP with JSON serialization
     *
     * @experimental
     */
    public const OTEL_COMPONENT_TYPE_VALUE_OTLP_HTTP_JSON_SPAN_EXPORTER = 'otlp_http_json_span_exporter';

    /**
     * Zipkin span exporter over HTTP
     *
     * @experimental
     */
    public const OTEL_COMPONENT_TYPE_VALUE_ZIPKIN_HTTP_SPAN_EXPORTER = 'zipkin_http_span_exporter';

    /**
     * OTLP log record exporter over gRPC with protobuf serialization
     *
     * @experimental
     */
    public const OTEL_COMPONENT_TYPE_VALUE_OTLP_GRPC_LOG_EXPORTER = 'otlp_grpc_log_exporter';

    /**
     * OTLP log record exporter over HTTP with protobuf serialization
     *
     * @experimental
     */
    public const OTEL_COMPONENT_TYPE_VALUE_OTLP_HTTP_LOG_EXPORTER = 'otlp_http_log_exporter';

    /**
     * OTLP log record exporter over HTTP with JSON serialization
     *
     * @experimental
     */
    public const OTEL_COMPONENT_TYPE_VALUE_OTLP_HTTP_JSON_LOG_EXPORTER = 'otlp_http_json_log_exporter';

    /**
     * The builtin SDK periodically exporting metric reader
     *
     * @experimental
     */
    public const OTEL_COMPONENT_TYPE_VALUE_PERIODIC_METRIC_READER = 'periodic_metric_reader';

    /**
     * OTLP metric exporter over gRPC with protobuf serialization
     *
     * @experimental
     */
    public const OTEL_COMPONENT_TYPE_VALUE_OTLP_GRPC_METRIC_EXPORTER = 'otlp_grpc_metric_exporter';

    /**
     * OTLP metric exporter over HTTP with protobuf serialization
     *
     * @experimental
     */
    public const OTEL_COMPONENT_TYPE_VALUE_OTLP_HTTP_METRIC_EXPORTER = 'otlp_http_metric_exporter';

    /**
     * OTLP metric exporter over HTTP with JSON serialization
     *
     * @experimental
     */
    public const OTEL_COMPONENT_TYPE_VALUE_OTLP_HTTP_JSON_METRIC_EXPORTER = 'otlp_http_json_metric_exporter';

    /**
     * Prometheus metric exporter over HTTP with the default text-based format
     *
     * @experimental
     */
    public const OTEL_COMPONENT_TYPE_VALUE_PROMETHEUS_HTTP_TEXT_METRIC_EXPORTER = 'prometheus_http_text_metric_exporter';

    /**
     * The name of the instrumentation scope - (`InstrumentationScope.Name` in OTLP).
     *
     * @stable
     */
    public const OTEL_SCOPE_NAME = 'otel.scope.name';

    /**
     * The schema URL of the instrumentation scope.
     *
     * @experimental
     */
    public const OTEL_SCOPE_SCHEMA_URL = 'otel.scope.schema_url';

    /**
     * The version of the instrumentation scope - (`InstrumentationScope.Version` in OTLP).
     *
     * @stable
     */
    public const OTEL_SCOPE_VERSION = 'otel.scope.version';

    /**
     * Determines whether the span has a parent span, and if so, [whether it is a remote parent](https://opentelemetry.io/docs/specs/otel/trace/api/#isremote)
     *
     * @experimental
     */
    public const OTEL_SPAN_PARENT_ORIGIN = 'otel.span.parent.origin';

    /**
     * The span does not have a parent, it is a root span
     * @experimental
     */
    public const OTEL_SPAN_PARENT_ORIGIN_VALUE_NONE = 'none';

    /**
     * The span has a parent and the parent's span context [isRemote()](https://opentelemetry.io/docs/specs/otel/trace/api/#isremote) is false
     * @experimental
     */
    public const OTEL_SPAN_PARENT_ORIGIN_VALUE_LOCAL = 'local';

    /**
     * The span has a parent and the parent's span context [isRemote()](https://opentelemetry.io/docs/specs/otel/trace/api/#isremote) is true
     * @experimental
     */
    public const OTEL_SPAN_PARENT_ORIGIN_VALUE_REMOTE = 'remote';

    /**
     * The result value of the sampler for this span
     *
     * @experimental
     */
    public const OTEL_SPAN_SAMPLING_RESULT = 'otel.span.sampling_result';

    /**
     * The span is not sampled and not recording
     * @experimental
     */
    public const OTEL_SPAN_SAMPLING_RESULT_VALUE_DROP = 'DROP';

    /**
     * The span is not sampled, but recording
     * @experimental
     */
    public const OTEL_SPAN_SAMPLING_RESULT_VALUE_RECORD_ONLY = 'RECORD_ONLY';

    /**
     * The span is sampled and recording
     * @experimental
     */
    public const OTEL_SPAN_SAMPLING_RESULT_VALUE_RECORD_AND_SAMPLE = 'RECORD_AND_SAMPLE';

    /**
     * Name of the code, either "OK" or "ERROR". MUST NOT be set if the status code is UNSET.
     *
     * @stable
     */
    public const OTEL_STATUS_CODE = 'otel.status_code';

    /**
     * The operation has been validated by an Application developer or Operator to have completed successfully.
     * @stable
     */
    public const OTEL_STATUS_CODE_VALUE_OK = 'OK';

    /**
     * The operation contains an error.
     * @stable
     */
    public const OTEL_STATUS_CODE_VALUE_ERROR = 'ERROR';

    /**
     * Description of the Status if it has a value, otherwise not set.
     *
     * @stable
     */
    public const OTEL_STATUS_DESCRIPTION = 'otel.status_description';

}
