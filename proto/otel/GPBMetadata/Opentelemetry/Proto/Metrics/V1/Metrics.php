<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: opentelemetry/proto/metrics/v1/metrics.proto

namespace GPBMetadata\Opentelemetry\Proto\Metrics\V1;

class Metrics
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
          return;
        }
        \GPBMetadata\Opentelemetry\Proto\Common\V1\Common::initOnce();
        \GPBMetadata\Opentelemetry\Proto\Resource\V1\Resource::initOnce();
        $pool->internalAddGeneratedFile(
            '
�
,opentelemetry/proto/metrics/v1/metrics.protoopentelemetry.proto.metrics.v1.opentelemetry/proto/resource/v1/resource.proto"X
MetricsDataI
resource_metrics (2/.opentelemetry.proto.metrics.v1.ResourceMetrics"�
ResourceMetrics;
resource (2).opentelemetry.proto.resource.v1.ResourceC
scope_metrics (2,.opentelemetry.proto.metrics.v1.ScopeMetrics

schema_url (	J��"�
ScopeMetricsB
scope (23.opentelemetry.proto.common.v1.InstrumentationScope7
metrics (2&.opentelemetry.proto.metrics.v1.Metric

schema_url (	"�
Metric
name (	
description (	
unit (	6
gauge (2%.opentelemetry.proto.metrics.v1.GaugeH 2
sum (2#.opentelemetry.proto.metrics.v1.SumH >
	histogram	 (2).opentelemetry.proto.metrics.v1.HistogramH U
exponential_histogram
 (24.opentelemetry.proto.metrics.v1.ExponentialHistogramH :
summary (2\'.opentelemetry.proto.metrics.v1.SummaryH B
dataJJJ	"M
GaugeD
data_points (2/.opentelemetry.proto.metrics.v1.NumberDataPoint"�
SumD
data_points (2/.opentelemetry.proto.metrics.v1.NumberDataPointW
aggregation_temporality (26.opentelemetry.proto.metrics.v1.AggregationTemporality
is_monotonic ("�
	HistogramG
data_points (22.opentelemetry.proto.metrics.v1.HistogramDataPointW
aggregation_temporality (26.opentelemetry.proto.metrics.v1.AggregationTemporality"�
ExponentialHistogramR
data_points (2=.opentelemetry.proto.metrics.v1.ExponentialHistogramDataPointW
aggregation_temporality (26.opentelemetry.proto.metrics.v1.AggregationTemporality"P
SummaryE
data_points (20.opentelemetry.proto.metrics.v1.SummaryDataPoint"�
NumberDataPoint;

attributes (2\'.opentelemetry.proto.common.v1.KeyValue
start_time_unix_nano (
time_unix_nano (
	as_double (H 
as_int (H ;
	exemplars (2(.opentelemetry.proto.metrics.v1.Exemplar
flags (B
valueJ"�
HistogramDataPoint;

attributes	 (2\'.opentelemetry.proto.common.v1.KeyValue
start_time_unix_nano (
time_unix_nano (
count (
sum (H �
bucket_counts (
explicit_bounds (;
	exemplars (2(.opentelemetry.proto.metrics.v1.Exemplar
flags
 (
min (H�
max (H�B
_sumB
_minB
_maxJ"�
ExponentialHistogramDataPoint;

attributes (2\'.opentelemetry.proto.common.v1.KeyValue
start_time_unix_nano (
time_unix_nano (
count (
sum (H �
scale (

zero_count (W
positive (2E.opentelemetry.proto.metrics.v1.ExponentialHistogramDataPoint.BucketsW
negative	 (2E.opentelemetry.proto.metrics.v1.ExponentialHistogramDataPoint.Buckets
flags
 (;
	exemplars (2(.opentelemetry.proto.metrics.v1.Exemplar
min (H�
max (H�0
Buckets
offset (
bucket_counts (B
_sumB
_minB
_max"�
SummaryDataPoint;

attributes (2\'.opentelemetry.proto.common.v1.KeyValue
start_time_unix_nano (
time_unix_nano (
count (
sum (Y
quantile_values (2@.opentelemetry.proto.metrics.v1.SummaryDataPoint.ValueAtQuantile
flags (2
ValueAtQuantile
quantile (
value (J"�
ExemplarD
filtered_attributes (2\'.opentelemetry.proto.common.v1.KeyValue
time_unix_nano (
	as_double (H 
as_int (H 
span_id (
trace_id (B
valueJ*�
AggregationTemporality\'
#AGGREGATION_TEMPORALITY_UNSPECIFIED !
AGGREGATION_TEMPORALITY_DELTA&
"AGGREGATION_TEMPORALITY_CUMULATIVE*;
DataPointFlags
	FLAG_NONE 
FLAG_NO_RECORDED_VALUEB
!io.opentelemetry.proto.metrics.v1BMetricsProtoPZ)go.opentelemetry.io/proto/otlp/metrics/v1�OpenTelemetry.Proto.Metrics.V1bproto3'
        , true);

        static::$is_initialized = true;
    }
}

