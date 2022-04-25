<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: opentelemetry/proto/trace/v1/trace.proto

namespace Opentelemetry\Proto\Trace\V1;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * A collection of InstrumentationLibrarySpans from a Resource.
 *
 * Generated from protobuf message <code>opentelemetry.proto.trace.v1.ResourceSpans</code>
 */
class ResourceSpans extends \Google\Protobuf\Internal\Message
{
    /**
     * The resource for the spans in this message.
     * If this field is not set then no resource info is known.
     *
     * Generated from protobuf field <code>.opentelemetry.proto.resource.v1.Resource resource = 1;</code>
     */
    protected $resource = null;
    /**
     * A list of InstrumentationLibrarySpans that originate from a resource.
     *
     * Generated from protobuf field <code>repeated .opentelemetry.proto.trace.v1.InstrumentationLibrarySpans instrumentation_library_spans = 2;</code>
     */
    private $instrumentation_library_spans;
    /**
     * This schema_url applies to the data in the "resource" field. It does not apply
     * to the data in the "instrumentation_library_spans" field which have their own
     * schema_url field.
     *
     * Generated from protobuf field <code>string schema_url = 3;</code>
     */
    protected $schema_url = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Opentelemetry\Proto\Resource\V1\Resource $resource
     *           The resource for the spans in this message.
     *           If this field is not set then no resource info is known.
     *     @type \Opentelemetry\Proto\Trace\V1\InstrumentationLibrarySpans[]|\Google\Protobuf\Internal\RepeatedField $instrumentation_library_spans
     *           A list of InstrumentationLibrarySpans that originate from a resource.
     *     @type string $schema_url
     *           This schema_url applies to the data in the "resource" field. It does not apply
     *           to the data in the "instrumentation_library_spans" field which have their own
     *           schema_url field.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Opentelemetry\Proto\Trace\V1\Trace::initOnce();
        parent::__construct($data);
    }

    /**
     * The resource for the spans in this message.
     * If this field is not set then no resource info is known.
     *
     * Generated from protobuf field <code>.opentelemetry.proto.resource.v1.Resource resource = 1;</code>
     * @return \Opentelemetry\Proto\Resource\V1\Resource|null
     */
    public function getResource()
    {
        return $this->resource;
    }

    public function hasResource()
    {
        return isset($this->resource);
    }

    public function clearResource()
    {
        unset($this->resource);
    }

    /**
     * The resource for the spans in this message.
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
     * A list of InstrumentationLibrarySpans that originate from a resource.
     *
     * Generated from protobuf field <code>repeated .opentelemetry.proto.trace.v1.InstrumentationLibrarySpans instrumentation_library_spans = 2;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getInstrumentationLibrarySpans()
    {
        return $this->instrumentation_library_spans;
    }

    /**
     * A list of InstrumentationLibrarySpans that originate from a resource.
     *
     * Generated from protobuf field <code>repeated .opentelemetry.proto.trace.v1.InstrumentationLibrarySpans instrumentation_library_spans = 2;</code>
     * @param \Opentelemetry\Proto\Trace\V1\InstrumentationLibrarySpans[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setInstrumentationLibrarySpans($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Opentelemetry\Proto\Trace\V1\InstrumentationLibrarySpans::class);
        $this->instrumentation_library_spans = $arr;

        return $this;
    }

    /**
     * This schema_url applies to the data in the "resource" field. It does not apply
     * to the data in the "instrumentation_library_spans" field which have their own
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
     * to the data in the "instrumentation_library_spans" field which have their own
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

