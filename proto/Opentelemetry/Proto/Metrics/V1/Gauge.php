<?php

declare(strict_types=1);
// Generated by the protocol buffer compiler.  DO NOT EDIT!
// source: opentelemetry/proto/metrics/v1/metrics.proto

namespace Opentelemetry\Proto\Metrics\V1;

use Google\Protobuf\Internal\GPBUtil;

/**
 * Gauge represents the type of a double scalar metric that always exports the
 * "current value" for every data point. It should be used for an "unknown"
 * aggregation.
 *
 * A Gauge does not support different aggregation temporalities. Given the
 * aggregation is unknown, points cannot be combined using the same
 * aggregation, regardless of aggregation temporalities. Therefore,
 * AggregationTemporality is not included. Consequently, this also means
 * "StartTimeUnixNano" is ignored for all data points.
 *
 * Generated from protobuf message <code>opentelemetry.proto.metrics.v1.Gauge</code>
 */
class Gauge extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>repeated .opentelemetry.proto.metrics.v1.NumberDataPoint data_points = 1;</code>
     */
    private $data_points;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Opentelemetry\Proto\Metrics\V1\NumberDataPoint[]|\Google\Protobuf\Internal\RepeatedField $data_points
     * }
     */
    public function __construct($data = null)
    {
        \GPBMetadata\Opentelemetry\Proto\Metrics\V1\Metrics::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>repeated .opentelemetry.proto.metrics.v1.NumberDataPoint data_points = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getDataPoints()
    {
        return $this->data_points;
    }

    /**
     * Generated from protobuf field <code>repeated .opentelemetry.proto.metrics.v1.NumberDataPoint data_points = 1;</code>
     * @param \Opentelemetry\Proto\Metrics\V1\NumberDataPoint[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setDataPoints($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Opentelemetry\Proto\Metrics\V1\NumberDataPoint::class);
        $this->data_points = $arr;

        return $this;
    }
}
