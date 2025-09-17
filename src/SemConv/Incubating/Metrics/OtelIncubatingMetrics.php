<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Unstable\Metrics;

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
     *
     * Instrument: counter
     * Unit: {log_record}
     * @experimental
     */
    public const OTEL_SDK_EXPORTER_LOG_EXPORTED = 'otel.sdk.exporter.log.exported';

    /**
     * The number of log records which were passed to the exporter, but that have not been exported yet (neither successful, nor failed).
     * For successful exports, `error.type` MUST NOT be set. For failed exports, `error.type` MUST contain the failure cause.
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
     *
     * Instrument: counter
     * Unit: {data_point}
     * @experimental
     */
    public const OTEL_SDK_EXPORTER_METRIC_DATA_POINT_EXPORTED = 'otel.sdk.exporter.metric_data_point.exported';

    /**
     * The number of metric data points which were passed to the exporter, but that have not been exported yet (neither successful, nor failed).
     * For successful exports, `error.type` MUST NOT be set. For failed exports, `error.type` MUST contain the failure cause.
     *
     * Instrument: updowncounter
     * Unit: {data_point}
     * @experimental
     */
    public const OTEL_SDK_EXPORTER_METRIC_DATA_POINT_INFLIGHT = 'otel.sdk.exporter.metric_data_point.inflight';

    /**
     * The duration of exporting a batch of telemetry records.
     * This metric defines successful operations using the full success definitions for [http](https://github.com/open-telemetry/opentelemetry-proto/blob/v1.5.0/docs/specification.md#full-success-1)
     * and [grpc](https://github.com/open-telemetry/opentelemetry-proto/blob/v1.5.0/docs/specification.md#full-success). Anything else is defined as an unsuccessful operation. For successful
     * operations, `error.type` MUST NOT be set. For unsuccessful export operations, `error.type` MUST contain a relevant failure cause.
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
     * For successful exports, `error.type` MUST NOT be set. For failed exports, `error.type` MUST contain the failure cause.
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
     * The number of logs submitted to enabled SDK Loggers.
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
     * For the SDK Simple and Batching Log Record Processor a log record is considered to be processed already when it has been submitted to the exporter,
     * not when the corresponding export call has finished.
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
     * For the SDK Simple and Batching Span Processor a span is considered to be processed already when it has been submitted to the exporter, not when the corresponding export call has finished.
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
