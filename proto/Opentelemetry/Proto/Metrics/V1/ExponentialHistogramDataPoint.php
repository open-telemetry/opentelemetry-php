<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: opentelemetry/proto/metrics/v1/metrics.proto

namespace Opentelemetry\Proto\Metrics\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * ExponentialHistogramDataPoint is a single data point in a timeseries that describes the
 * time-varying values of a ExponentialHistogram of double values. A ExponentialHistogram contains
 * summary statistics for a population of values, it may optionally contain the
 * distribution of those values across a set of buckets.
 *
 * Generated from protobuf message <code>opentelemetry.proto.metrics.v1.ExponentialHistogramDataPoint</code>
 */
class ExponentialHistogramDataPoint extends \Google\Protobuf\Internal\Message
{
    /**
     * The set of key/value pairs that uniquely identify the timeseries from
     * where this point belongs. The list may be empty (may contain 0 elements).
     *
     * Generated from protobuf field <code>repeated .opentelemetry.proto.common.v1.KeyValue attributes = 1;</code>
     */
    private $attributes;
    /**
     * StartTimeUnixNano is optional but strongly encouraged, see the
     * the detailed comments above Metric.
     * Value is UNIX Epoch time in nanoseconds since 00:00:00 UTC on 1 January
     * 1970.
     *
     * Generated from protobuf field <code>fixed64 start_time_unix_nano = 2;</code>
     */
    private $start_time_unix_nano = 0;
    /**
     * TimeUnixNano is required, see the detailed comments above Metric.
     * Value is UNIX Epoch time in nanoseconds since 00:00:00 UTC on 1 January
     * 1970.
     *
     * Generated from protobuf field <code>fixed64 time_unix_nano = 3;</code>
     */
    private $time_unix_nano = 0;
    /**
     * count is the number of values in the population. Must be
     * non-negative. This value must be equal to the sum of the "bucket_counts"
     * values in the positive and negative Buckets plus the "zero_count" field.
     *
     * Generated from protobuf field <code>fixed64 count = 4;</code>
     */
    private $count = 0;
    /**
     * sum of the values in the population. If count is zero then this field
     * must be zero.
     * Note: Sum should only be filled out when measuring non-negative discrete
     * events, and is assumed to be monotonic over the values of these events.
     * Negative events *can* be recorded, but sum should not be filled out when
     * doing so.  This is specifically to enforce compatibility w/ OpenMetrics,
     * see: https://github.com/OpenObservability/OpenMetrics/blob/main/specification/OpenMetrics.md#histogram
     *
     * Generated from protobuf field <code>double sum = 5;</code>
     */
    private $sum = 0.0;
    /**
     * scale describes the resolution of the histogram.  Boundaries are
     * located at powers of the base, where:
     *   base = (2^(2^-scale))
     * The histogram bucket identified by `index`, a signed integer,
     * contains values that are greater than or equal to (base^index) and
     * less than (base^(index+1)).
     * The positive and negative ranges of the histogram are expressed
     * separately.  Negative values are mapped by their absolute value
     * into the negative range using the same scale as the positive range.
     * scale is not restricted by the protocol, as the permissible
     * values depend on the range of the data.
     *
     * Generated from protobuf field <code>sint32 scale = 6;</code>
     */
    private $scale = 0;
    /**
     * zero_count is the count of values that are either exactly zero or
     * within the region considered zero by the instrumentation at the
     * tolerated degree of precision.  This bucket stores values that
     * cannot be expressed using the standard exponential formula as
     * well as values that have been rounded to zero.
     * Implementations MAY consider the zero bucket to have probability
     * mass equal to (zero_count / count).
     *
     * Generated from protobuf field <code>fixed64 zero_count = 7;</code>
     */
    private $zero_count = 0;
    /**
     * positive carries the positive range of exponential bucket counts.
     *
     * Generated from protobuf field <code>.opentelemetry.proto.metrics.v1.ExponentialHistogramDataPoint.Buckets positive = 8;</code>
     */
    private $positive = null;
    /**
     * negative carries the negative range of exponential bucket counts.
     *
     * Generated from protobuf field <code>.opentelemetry.proto.metrics.v1.ExponentialHistogramDataPoint.Buckets negative = 9;</code>
     */
    private $negative = null;
    /**
     * Flags that apply to this specific data point.  See DataPointFlags
     * for the available flags and their meaning.
     *
     * Generated from protobuf field <code>uint32 flags = 10;</code>
     */
    private $flags = 0;
    /**
     * (Optional) List of exemplars collected from
     * measurements that were used to form the data point
     *
     * Generated from protobuf field <code>repeated .opentelemetry.proto.metrics.v1.Exemplar exemplars = 11;</code>
     */
    private $exemplars;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Opentelemetry\Proto\Common\V1\KeyValue[]|\Google\Protobuf\Internal\RepeatedField $attributes
     *           The set of key/value pairs that uniquely identify the timeseries from
     *           where this point belongs. The list may be empty (may contain 0 elements).
     *     @type int|string $start_time_unix_nano
     *           StartTimeUnixNano is optional but strongly encouraged, see the
     *           the detailed comments above Metric.
     *           Value is UNIX Epoch time in nanoseconds since 00:00:00 UTC on 1 January
     *           1970.
     *     @type int|string $time_unix_nano
     *           TimeUnixNano is required, see the detailed comments above Metric.
     *           Value is UNIX Epoch time in nanoseconds since 00:00:00 UTC on 1 January
     *           1970.
     *     @type int|string $count
     *           count is the number of values in the population. Must be
     *           non-negative. This value must be equal to the sum of the "bucket_counts"
     *           values in the positive and negative Buckets plus the "zero_count" field.
     *     @type float $sum
     *           sum of the values in the population. If count is zero then this field
     *           must be zero.
     *           Note: Sum should only be filled out when measuring non-negative discrete
     *           events, and is assumed to be monotonic over the values of these events.
     *           Negative events *can* be recorded, but sum should not be filled out when
     *           doing so.  This is specifically to enforce compatibility w/ OpenMetrics,
     *           see: https://github.com/OpenObservability/OpenMetrics/blob/main/specification/OpenMetrics.md#histogram
     *     @type int $scale
     *           scale describes the resolution of the histogram.  Boundaries are
     *           located at powers of the base, where:
     *             base = (2^(2^-scale))
     *           The histogram bucket identified by `index`, a signed integer,
     *           contains values that are greater than or equal to (base^index) and
     *           less than (base^(index+1)).
     *           The positive and negative ranges of the histogram are expressed
     *           separately.  Negative values are mapped by their absolute value
     *           into the negative range using the same scale as the positive range.
     *           scale is not restricted by the protocol, as the permissible
     *           values depend on the range of the data.
     *     @type int|string $zero_count
     *           zero_count is the count of values that are either exactly zero or
     *           within the region considered zero by the instrumentation at the
     *           tolerated degree of precision.  This bucket stores values that
     *           cannot be expressed using the standard exponential formula as
     *           well as values that have been rounded to zero.
     *           Implementations MAY consider the zero bucket to have probability
     *           mass equal to (zero_count / count).
     *     @type \Opentelemetry\Proto\Metrics\V1\ExponentialHistogramDataPoint\Buckets $positive
     *           positive carries the positive range of exponential bucket counts.
     *     @type \Opentelemetry\Proto\Metrics\V1\ExponentialHistogramDataPoint\Buckets $negative
     *           negative carries the negative range of exponential bucket counts.
     *     @type int $flags
     *           Flags that apply to this specific data point.  See DataPointFlags
     *           for the available flags and their meaning.
     *     @type \Opentelemetry\Proto\Metrics\V1\Exemplar[]|\Google\Protobuf\Internal\RepeatedField $exemplars
     *           (Optional) List of exemplars collected from
     *           measurements that were used to form the data point
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Opentelemetry\Proto\Metrics\V1\Metrics::initOnce();
        parent::__construct($data);
    }

    /**
     * The set of key/value pairs that uniquely identify the timeseries from
     * where this point belongs. The list may be empty (may contain 0 elements).
     *
     * Generated from protobuf field <code>repeated .opentelemetry.proto.common.v1.KeyValue attributes = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * The set of key/value pairs that uniquely identify the timeseries from
     * where this point belongs. The list may be empty (may contain 0 elements).
     *
     * Generated from protobuf field <code>repeated .opentelemetry.proto.common.v1.KeyValue attributes = 1;</code>
     * @param \Opentelemetry\Proto\Common\V1\KeyValue[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setAttributes($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Opentelemetry\Proto\Common\V1\KeyValue::class);
        $this->attributes = $arr;

        return $this;
    }

    /**
     * StartTimeUnixNano is optional but strongly encouraged, see the
     * the detailed comments above Metric.
     * Value is UNIX Epoch time in nanoseconds since 00:00:00 UTC on 1 January
     * 1970.
     *
     * Generated from protobuf field <code>fixed64 start_time_unix_nano = 2;</code>
     * @return int|string
     */
    public function getStartTimeUnixNano()
    {
        return $this->start_time_unix_nano;
    }

    /**
     * StartTimeUnixNano is optional but strongly encouraged, see the
     * the detailed comments above Metric.
     * Value is UNIX Epoch time in nanoseconds since 00:00:00 UTC on 1 January
     * 1970.
     *
     * Generated from protobuf field <code>fixed64 start_time_unix_nano = 2;</code>
     * @param int|string $var
     * @return $this
     */
    public function setStartTimeUnixNano($var)
    {
        GPBUtil::checkUint64($var);
        $this->start_time_unix_nano = $var;

        return $this;
    }

    /**
     * TimeUnixNano is required, see the detailed comments above Metric.
     * Value is UNIX Epoch time in nanoseconds since 00:00:00 UTC on 1 January
     * 1970.
     *
     * Generated from protobuf field <code>fixed64 time_unix_nano = 3;</code>
     * @return int|string
     */
    public function getTimeUnixNano()
    {
        return $this->time_unix_nano;
    }

    /**
     * TimeUnixNano is required, see the detailed comments above Metric.
     * Value is UNIX Epoch time in nanoseconds since 00:00:00 UTC on 1 January
     * 1970.
     *
     * Generated from protobuf field <code>fixed64 time_unix_nano = 3;</code>
     * @param int|string $var
     * @return $this
     */
    public function setTimeUnixNano($var)
    {
        GPBUtil::checkUint64($var);
        $this->time_unix_nano = $var;

        return $this;
    }

    /**
     * count is the number of values in the population. Must be
     * non-negative. This value must be equal to the sum of the "bucket_counts"
     * values in the positive and negative Buckets plus the "zero_count" field.
     *
     * Generated from protobuf field <code>fixed64 count = 4;</code>
     * @return int|string
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * count is the number of values in the population. Must be
     * non-negative. This value must be equal to the sum of the "bucket_counts"
     * values in the positive and negative Buckets plus the "zero_count" field.
     *
     * Generated from protobuf field <code>fixed64 count = 4;</code>
     * @param int|string $var
     * @return $this
     */
    public function setCount($var)
    {
        GPBUtil::checkUint64($var);
        $this->count = $var;

        return $this;
    }

    /**
     * sum of the values in the population. If count is zero then this field
     * must be zero.
     * Note: Sum should only be filled out when measuring non-negative discrete
     * events, and is assumed to be monotonic over the values of these events.
     * Negative events *can* be recorded, but sum should not be filled out when
     * doing so.  This is specifically to enforce compatibility w/ OpenMetrics,
     * see: https://github.com/OpenObservability/OpenMetrics/blob/main/specification/OpenMetrics.md#histogram
     *
     * Generated from protobuf field <code>double sum = 5;</code>
     * @return float
     */
    public function getSum()
    {
        return $this->sum;
    }

    /**
     * sum of the values in the population. If count is zero then this field
     * must be zero.
     * Note: Sum should only be filled out when measuring non-negative discrete
     * events, and is assumed to be monotonic over the values of these events.
     * Negative events *can* be recorded, but sum should not be filled out when
     * doing so.  This is specifically to enforce compatibility w/ OpenMetrics,
     * see: https://github.com/OpenObservability/OpenMetrics/blob/main/specification/OpenMetrics.md#histogram
     *
     * Generated from protobuf field <code>double sum = 5;</code>
     * @param float $var
     * @return $this
     */
    public function setSum($var)
    {
        GPBUtil::checkDouble($var);
        $this->sum = $var;

        return $this;
    }

    /**
     * scale describes the resolution of the histogram.  Boundaries are
     * located at powers of the base, where:
     *   base = (2^(2^-scale))
     * The histogram bucket identified by `index`, a signed integer,
     * contains values that are greater than or equal to (base^index) and
     * less than (base^(index+1)).
     * The positive and negative ranges of the histogram are expressed
     * separately.  Negative values are mapped by their absolute value
     * into the negative range using the same scale as the positive range.
     * scale is not restricted by the protocol, as the permissible
     * values depend on the range of the data.
     *
     * Generated from protobuf field <code>sint32 scale = 6;</code>
     * @return int
     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * scale describes the resolution of the histogram.  Boundaries are
     * located at powers of the base, where:
     *   base = (2^(2^-scale))
     * The histogram bucket identified by `index`, a signed integer,
     * contains values that are greater than or equal to (base^index) and
     * less than (base^(index+1)).
     * The positive and negative ranges of the histogram are expressed
     * separately.  Negative values are mapped by their absolute value
     * into the negative range using the same scale as the positive range.
     * scale is not restricted by the protocol, as the permissible
     * values depend on the range of the data.
     *
     * Generated from protobuf field <code>sint32 scale = 6;</code>
     * @param int $var
     * @return $this
     */
    public function setScale($var)
    {
        GPBUtil::checkInt32($var);
        $this->scale = $var;

        return $this;
    }

    /**
     * zero_count is the count of values that are either exactly zero or
     * within the region considered zero by the instrumentation at the
     * tolerated degree of precision.  This bucket stores values that
     * cannot be expressed using the standard exponential formula as
     * well as values that have been rounded to zero.
     * Implementations MAY consider the zero bucket to have probability
     * mass equal to (zero_count / count).
     *
     * Generated from protobuf field <code>fixed64 zero_count = 7;</code>
     * @return int|string
     */
    public function getZeroCount()
    {
        return $this->zero_count;
    }

    /**
     * zero_count is the count of values that are either exactly zero or
     * within the region considered zero by the instrumentation at the
     * tolerated degree of precision.  This bucket stores values that
     * cannot be expressed using the standard exponential formula as
     * well as values that have been rounded to zero.
     * Implementations MAY consider the zero bucket to have probability
     * mass equal to (zero_count / count).
     *
     * Generated from protobuf field <code>fixed64 zero_count = 7;</code>
     * @param int|string $var
     * @return $this
     */
    public function setZeroCount($var)
    {
        GPBUtil::checkUint64($var);
        $this->zero_count = $var;

        return $this;
    }

    /**
     * positive carries the positive range of exponential bucket counts.
     *
     * Generated from protobuf field <code>.opentelemetry.proto.metrics.v1.ExponentialHistogramDataPoint.Buckets positive = 8;</code>
     * @return \Opentelemetry\Proto\Metrics\V1\ExponentialHistogramDataPoint\Buckets
     */
    public function getPositive()
    {
        return $this->positive;
    }

    /**
     * positive carries the positive range of exponential bucket counts.
     *
     * Generated from protobuf field <code>.opentelemetry.proto.metrics.v1.ExponentialHistogramDataPoint.Buckets positive = 8;</code>
     * @param \Opentelemetry\Proto\Metrics\V1\ExponentialHistogramDataPoint\Buckets $var
     * @return $this
     */
    public function setPositive($var)
    {
        GPBUtil::checkMessage($var, \Opentelemetry\Proto\Metrics\V1\ExponentialHistogramDataPoint_Buckets::class);
        $this->positive = $var;

        return $this;
    }

    /**
     * negative carries the negative range of exponential bucket counts.
     *
     * Generated from protobuf field <code>.opentelemetry.proto.metrics.v1.ExponentialHistogramDataPoint.Buckets negative = 9;</code>
     * @return \Opentelemetry\Proto\Metrics\V1\ExponentialHistogramDataPoint\Buckets
     */
    public function getNegative()
    {
        return $this->negative;
    }

    /**
     * negative carries the negative range of exponential bucket counts.
     *
     * Generated from protobuf field <code>.opentelemetry.proto.metrics.v1.ExponentialHistogramDataPoint.Buckets negative = 9;</code>
     * @param \Opentelemetry\Proto\Metrics\V1\ExponentialHistogramDataPoint\Buckets $var
     * @return $this
     */
    public function setNegative($var)
    {
        GPBUtil::checkMessage($var, \Opentelemetry\Proto\Metrics\V1\ExponentialHistogramDataPoint_Buckets::class);
        $this->negative = $var;

        return $this;
    }

    /**
     * Flags that apply to this specific data point.  See DataPointFlags
     * for the available flags and their meaning.
     *
     * Generated from protobuf field <code>uint32 flags = 10;</code>
     * @return int
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Flags that apply to this specific data point.  See DataPointFlags
     * for the available flags and their meaning.
     *
     * Generated from protobuf field <code>uint32 flags = 10;</code>
     * @param int $var
     * @return $this
     */
    public function setFlags($var)
    {
        GPBUtil::checkUint32($var);
        $this->flags = $var;

        return $this;
    }

    /**
     * (Optional) List of exemplars collected from
     * measurements that were used to form the data point
     *
     * Generated from protobuf field <code>repeated .opentelemetry.proto.metrics.v1.Exemplar exemplars = 11;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getExemplars()
    {
        return $this->exemplars;
    }

    /**
     * (Optional) List of exemplars collected from
     * measurements that were used to form the data point
     *
     * Generated from protobuf field <code>repeated .opentelemetry.proto.metrics.v1.Exemplar exemplars = 11;</code>
     * @param \Opentelemetry\Proto\Metrics\V1\Exemplar[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setExemplars($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Opentelemetry\Proto\Metrics\V1\Exemplar::class);
        $this->exemplars = $arr;

        return $this;
    }

}

