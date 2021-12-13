<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: opentelemetry/proto/metrics/v1/metrics.proto

namespace Opentelemetry\Proto\Metrics\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * A collection of InstrumentationLibraryMetrics from a Resource.
 *
 * Generated from protobuf message <code>opentelemetry.proto.metrics.v1.ResourceMetrics</code>
 */
class ResourceMetrics extends \Google\Protobuf\Internal\Message
{
    /**
     * The resource for the metrics in this message.
     * If this field is not set then no resource info is known.
     *
     * Generated from protobuf field <code>.opentelemetry.proto.resource.v1.Resource resource = 1;</code>
     */
    private $resource = null;
    /**
     * A list of metrics that originate from a resource.
     *
     * Generated from protobuf field <code>repeated .opentelemetry.proto.metrics.v1.InstrumentationLibraryMetrics instrumentation_library_metrics = 2;</code>
     */
    private $instrumentation_library_metrics;
    /**
     * This schema_url applies to the data in the "resource" field. It does not apply
     * to the data in the "instrumentation_library_metrics" field which have their own
     * schema_url field.
     *
     * Generated from protobuf field <code>string schema_url = 3;</code>
     */
    private $schema_url = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Opentelemetry\Proto\Resource\V1\Resource $resource
     *           The resource for the metrics in this message.
     *           If this field is not set then no resource info is known.
     *     @type \Opentelemetry\Proto\Metrics\V1\InstrumentationLibraryMetrics[]|\Google\Protobuf\Internal\RepeatedField $instrumentation_library_metrics
     *           A list of metrics that originate from a resource.
     *     @type string $schema_url
     *           This schema_url applies to the data in the "resource" field. It does not apply
     *           to the data in the "instrumentation_library_metrics" field which have their own
     *           schema_url field.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Opentelemetry\Proto\Metrics\V1\Metrics::initOnce();
        parent::__construct($data);
    }

    /**
     * The resource for the metrics in this message.
     * If this field is not set then no resource info is known.
     *
     * Generated from protobuf field <code>.opentelemetry.proto.resource.v1.Resource resource = 1;</code>
     * @return \Opentelemetry\Proto\Resource\V1\Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * The resource for the metrics in this message.
     * If this field is not set then no resource info is known.
     *
     * Generated from protobuf field <code>.opentelemetry.proto.resource.v1.Resource resource = 1;</code>
     * @param \Opentelemetry\Proto\Resource\V1\Resource $var
     * @return $this
     */
    public function setResource($var)
    {
        GPBUtil::checkMessage($var, \Opentelemetry\Proto\Resource\V1\Resource::class);
        $this->resource = $var;

        return $this;
    }

    /**
     * A list of metrics that originate from a resource.
     *
     * Generated from protobuf field <code>repeated .opentelemetry.proto.metrics.v1.InstrumentationLibraryMetrics instrumentation_library_metrics = 2;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getInstrumentationLibraryMetrics()
    {
        return $this->instrumentation_library_metrics;
    }

    /**
     * A list of metrics that originate from a resource.
     *
     * Generated from protobuf field <code>repeated .opentelemetry.proto.metrics.v1.InstrumentationLibraryMetrics instrumentation_library_metrics = 2;</code>
     * @param \Opentelemetry\Proto\Metrics\V1\InstrumentationLibraryMetrics[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setInstrumentationLibraryMetrics($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Opentelemetry\Proto\Metrics\V1\InstrumentationLibraryMetrics::class);
        $this->instrumentation_library_metrics = $arr;

        return $this;
    }

    /**
     * This schema_url applies to the data in the "resource" field. It does not apply
     * to the data in the "instrumentation_library_metrics" field which have their own
     * schema_url field.
     *
     * Generated from protobuf field <code>string schema_url = 3;</code>
     * @return string
     */
    public function getSchemaUrl()
    {
        return $this->schema_url;
    }

    /**
     * This schema_url applies to the data in the "resource" field. It does not apply
     * to the data in the "instrumentation_library_metrics" field which have their own
     * schema_url field.
     *
     * Generated from protobuf field <code>string schema_url = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setSchemaUrl($var)
    {
        GPBUtil::checkString($var, True);
        $this->schema_url = $var;

        return $this;
    }

}

