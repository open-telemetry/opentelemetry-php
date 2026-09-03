<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Metrics;

/**
 * Metrics for otel.
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface OtelIncubatingMetrics
{
    /**
     * The number of log records for which the export has finished, either successful or failed.
     * For successful exports, `error.type` MUST NOT be set. For failed exports, `error.type` MUST contain the failure cause.
     * For exporters with partial success semantics (e.g. OTLP with `rejected_log_records`), rejected log records MUST count as failed and only non-rejected log records count as success.
     * If no rejection reason is available, `rejected` SHOULD be used as value for `error.type`.
     * If the exporter retries failed export attempts, the export operation is considered finished only after the final attempt has concluded.
     * Each log record MUST be counted exactly once per export operation: intermediate failed attempts that are followed by a retry MUST NOT increment the counter,
     * and `error.type` reflects the cause of the final attempt.
     *
     * Instrument: counter
     * Unit: {log_record}
     * @experimental
     */
    public const OTEL_SDK_EXPORTER_LOG_EXPORTED = 'otel.sdk.exporter.log.exported';

    /**
     * The number of log records which were passed to the exporter, but that have not been exported yet (neither successful, nor failed).
     * Log records are counted as inflight from when they are passed to the exporter until the export operation has concluded.
     * If the exporter retries failed export attempts, log records remain inflight across all retry attempts and any backoff between them.
     *
     * Instrument: updowncounter
     * Unit: {log_record}
     * @experimental
     */
    public const OTEL_SDK_EXPORTER_LOG_INFLIGHT = 'otel.sdk.exporter.log.inflight';

    /**
     * The number of metric data points for which the export has finished, either successful or failed.
     * For successful exports, `error.type` MUST NOT be set. For failed exports, `error.type` MUST contain the failure cause.
     * For exporters with partial success semantics (e.g. OTLP with `rejected_data_points`), rejected data points MUST count as failed and only non-rejected data points count as success.
     * If no rejection reason is available, `rejected` SHOULD be used as value for `error.type`.
     * If the exporter retries failed export attempts, the export operation is considered finished only after the final attempt has concluded.
     * Each metric data point MUST be counted exactly once per export operation: intermediate failed attempts that are followed by a retry MUST NOT increment the counter,
     * and `error.type` reflects the cause of the final attempt.
     *
     * Instrument: counter
     * Unit: {data_point}
     * @experimental
     */
    public const OTEL_SDK_EXPORTER_METRIC_DATA_POINT_EXPORTED = 'otel.sdk.exporter.metric_data_point.exported';

    /**
     * The number of metric data points which were passed to the exporter, but that have not been exported yet (neither successful, nor failed).
     * Metric data points are counted as inflight from when they are passed to the exporter until the export operation has concluded.
     * If the exporter retries failed export attempts, metric data points remain inflight across all retry attempts and any backoff between them.
     *
     * Instrument: updowncounter
     * Unit: {data_point}
     * @experimental
     */
    public const OTEL_SDK_EXPORTER_METRIC_DATA_POINT_INFLIGHT = 'otel.sdk.exporter.metric_data_point.inflight';

    /**
     * The duration of exporting a batch of telemetry records.
     * This metric defines successful operations using the full success definitions for [HTTP](https://github.com/open-telemetry/opentelemetry-proto/blob/v1.5.0/docs/specification.md#full-success-1)
     * and [gRPC](https://github.com/open-telemetry/opentelemetry-proto/blob/v1.5.0/docs/specification.md#full-success). Anything else is defined as an unsuccessful operation. For successful
     * operations, `error.type` MUST NOT be set. For unsuccessful export operations, `error.type` MUST contain a relevant failure cause.
     * If the exporter retries failed export attempts, exactly one observation MUST be recorded per export operation,
     * covering the wall-clock duration from the start of the first attempt through the conclusion of the final attempt (including any backoff between attempts).
     * `error.type` reflects the cause of the final attempt.
     *
     * Instrument: histogram
     * Unit: s
     * @experimental
     */
    public const OTEL_SDK_EXPORTER_OPERATION_DURATION = 'otel.sdk.exporter.operation.duration';

    /**
     * The number of spans for which the export has finished, either successful or failed.
     * For successful exports, `error.type` MUST NOT be set. For failed exports, `error.type` MUST contain the failure cause.
     * For exporters with partial success semantics (e.g. OTLP with `rejected_spans`), rejected spans MUST count as failed and only non-rejected spans count as success.
     * If no rejection reason is available, `rejected` SHOULD be used as value for `error.type`.
     * If the exporter retries failed export attempts, the export operation is considered finished only after the final attempt has concluded.
     * Each span MUST be counted exactly once per export operation: intermediate failed attempts that are followed by a retry MUST NOT increment the counter,
     * and `error.type` reflects the cause of the final attempt.
     *
     * Instrument: counter
     * Unit: {span}
     * @experimental
     */
    public const OTEL_SDK_EXPORTER_SPAN_EXPORTED = 'otel.sdk.exporter.span.exported';

    /**
     * Deprecated, use `otel.sdk.exporter.span.exported` instead.
     *
     * Instrument: updowncounter
     * Unit: {span}
     * @experimental
     */
    public const OTEL_SDK_EXPORTER_SPAN_EXPORTED_COUNT = 'otel.sdk.exporter.span.exported.count';

    /**
     * The number of spans which were passed to the exporter, but that have not been exported yet (neither successful, nor failed).
     * Spans are counted as inflight from when they are passed to the exporter until the export operation has concluded.
     * If the exporter retries failed export attempts, spans remain inflight across all retry attempts and any backoff between them.
     *
     * Instrument: updowncounter
     * Unit: {span}
     * @experimental
     */
    public const OTEL_SDK_EXPORTER_SPAN_INFLIGHT = 'otel.sdk.exporter.span.inflight';

    /**
     * Deprecated, use `otel.sdk.exporter.span.inflight` instead.
     *
     * Instrument: updowncounter
     * Unit: {span}
     * @experimental
     */
    public const OTEL_SDK_EXPORTER_SPAN_INFLIGHT_COUNT = 'otel.sdk.exporter.span.inflight.count';

    /**
     * The number of log records submitted to an enabled `Logger`.
     * In OpenTelemetry SDKs a `Logger` is enabled by default, and can be disabled via configuration i.e. `LoggerConfig.enabled` = `false` when supported;
     * a disabled `Logger` is a No-op: emitting to it has no effect, so its records are not counted.
     * Every log record submitted to an enabled `Logger` is counted, even if it is later filtered or dropped within the SDK
     * (e.g. by minimum severity or trace-based rules, or by a processor or the export pipeline), making this metric the top of the log delivery funnel.
     * Records not submitted to the SDK are not counted (e.g. a caller that skips calling `Emit()` based on an `Enabled()` check, or an upstream logging library that filters first).
     *
     * Instrument: counter
     * Unit: {log_record}
     * @experimental
     */
    public const OTEL_SDK_LOG_CREATED = 'otel.sdk.log.created';

    /**
     * The duration of the collect operation of the metric reader.
     * For successful collections, `error.type` MUST NOT be set. For failed collections, `error.type` SHOULD contain the failure cause.
     * It can happen that metrics collection is successful for some MetricProducers, while others fail. In that case `error.type` SHOULD be set to any of the failure causes.
     *
     * Instrument: histogram
     * Unit: s
     * @experimental
     */
    public const OTEL_SDK_METRIC_READER_COLLECTION_DURATION = 'otel.sdk.metric_reader.collection.duration';

    /**
     * The number of log records for which the processing has finished, either successful or failed.
     * For successful processing, `error.type` MUST NOT be set. For failed processing, `error.type` MUST contain the failure cause.
     * SDK Batching Log Record Processors MUST use `queue_full` as the value of `error.type` for log records dropped due to a full queue.
     * If a processor reports a log record dropped because it has already been shut down, `error.type` MUST be `already_shutdown`.
     * Whether and when a processor drops such log records is governed by the SDK specification, not by this metric.
     * For the SDK Simple and Batching Log Record Processors, a log record MUST be counted as successfully processed at the point the
     * processor invokes the export operation. For batching processors, all log records in the batch passed to the exporter are counted
     * at that point; log records accepted into the processor's queue but not yet passed to the exporter have not been processed.
     * Implementations MUST NOT delay this count until the export operation concludes, and the outcome of the export operation,
     * including an immediate failure of the invocation itself, MUST NOT affect this metric.
     * Export outcomes are reported by `otel.sdk.exporter.log.exported`.
     *
     * Instrument: counter
     * Unit: {log_record}
     * @experimental
     */
    public const OTEL_SDK_PROCESSOR_LOG_PROCESSED = 'otel.sdk.processor.log.processed';

    /**
     * The maximum number of log records the queue of a given instance of an SDK Log Record processor can hold.
     * Only applies to Log Record processors which use a queue, e.g. the SDK Batching Log Record Processor.
     *
     * Instrument: updowncounter
     * Unit: {log_record}
     * @experimental
     */
    public const OTEL_SDK_PROCESSOR_LOG_QUEUE_CAPACITY = 'otel.sdk.processor.log.queue.capacity';

    /**
     * The number of log records in the queue of a given instance of an SDK log processor.
     * Only applies to log record processors which use a queue, e.g. the SDK Batching Log Record Processor.
     *
     * Instrument: updowncounter
     * Unit: {log_record}
     * @experimental
     */
    public const OTEL_SDK_PROCESSOR_LOG_QUEUE_SIZE = 'otel.sdk.processor.log.queue.size';

    /**
     * The number of spans for which the processing has finished, either successful or failed.
     * For successful processing, `error.type` MUST NOT be set. For failed processing, `error.type` MUST contain the failure cause.
     * SDK Batching Span Processors MUST use `queue_full` as the value of `error.type` for spans dropped due to a full queue.
     * If a processor reports a span dropped because it has already been shut down, `error.type` MUST be `already_shutdown`.
     * Whether and when a processor drops such spans is governed by the SDK specification, not by this metric.
     * For the SDK Simple and Batching Span Processors, a span MUST be counted as successfully processed at the point the processor
     * invokes the export operation. For batching processors, all spans in the batch passed to the exporter are counted at that point;
     * spans accepted into the processor's queue but not yet passed to the exporter have not been processed.
     * Implementations MUST NOT delay this count until the export operation concludes, and the outcome of the export operation,
     * including an immediate failure of the invocation itself, MUST NOT affect this metric.
     * Export outcomes are reported by `otel.sdk.exporter.span.exported`.
     *
     * Instrument: counter
     * Unit: {span}
     * @experimental
     */
    public const OTEL_SDK_PROCESSOR_SPAN_PROCESSED = 'otel.sdk.processor.span.processed';

    /**
     * Deprecated, use `otel.sdk.processor.span.processed` instead.
     *
     * Instrument: updowncounter
     * Unit: {span}
     * @experimental
     */
    public const OTEL_SDK_PROCESSOR_SPAN_PROCESSED_COUNT = 'otel.sdk.processor.span.processed.count';

    /**
     * The maximum number of spans the queue of a given instance of an SDK span processor can hold.
     * Only applies to span processors which use a queue, e.g. the SDK Batching Span Processor.
     *
     * Instrument: updowncounter
     * Unit: {span}
     * @experimental
     */
    public const OTEL_SDK_PROCESSOR_SPAN_QUEUE_CAPACITY = 'otel.sdk.processor.span.queue.capacity';

    /**
     * The number of spans in the queue of a given instance of an SDK span processor.
     * Only applies to span processors which use a queue, e.g. the SDK Batching Span Processor.
     *
     * Instrument: updowncounter
     * Unit: {span}
     * @experimental
     */
    public const OTEL_SDK_PROCESSOR_SPAN_QUEUE_SIZE = 'otel.sdk.processor.span.queue.size';

    /**
     * Use `otel.sdk.span.started` minus `otel.sdk.span.live` to derive this value.
     *
     * Instrument: counter
     * Unit: {span}
     * @experimental
     */
    public const OTEL_SDK_SPAN_ENDED = 'otel.sdk.span.ended';

    /**
     * Use `otel.sdk.span.started` minus `otel.sdk.span.live` to derive this value.
     *
     * Instrument: counter
     * Unit: {span}
     * @experimental
     */
    public const OTEL_SDK_SPAN_ENDED_COUNT = 'otel.sdk.span.ended.count';

    /**
     * The number of created spans with `recording=true` for which the end operation has not been called yet.
     * Non-recording spans are not counted, hence `otel.span.sampling_result` can only take values `RECORD_ONLY` and `RECORD_AND_SAMPLE`, not `DROP`.
     *
     * Instrument: updowncounter
     * Unit: {span}
     * @experimental
     */
    public const OTEL_SDK_SPAN_LIVE = 'otel.sdk.span.live';

    /**
     * Deprecated, use `otel.sdk.span.live` instead.
     *
     * Instrument: updowncounter
     * Unit: {span}
     * @experimental
     */
    public const OTEL_SDK_SPAN_LIVE_COUNT = 'otel.sdk.span.live.count';

    /**
     * The number of created spans.
     * Implementations MUST record this metric for all spans, even for non-recording ones.
     *
     * Instrument: counter
     * Unit: {span}
     * @experimental
     */
    public const OTEL_SDK_SPAN_STARTED = 'otel.sdk.span.started';

}
