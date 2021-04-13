<?php

declare(strict_types=1);
// Generated by the protocol buffer compiler.  DO NOT EDIT!
// source: opentelemetry/proto/resource/v1/resource.proto

namespace Opentelemetry\Proto\Resource\V1;

use Google\Protobuf\Internal\GPBUtil;

/**
 * Resource information.
 *
 * Generated from protobuf message <code>opentelemetry.proto.resource.v1.Resource</code>
 */
class Resource extends \Google\Protobuf\Internal\Message
{
    /**
     * Set of labels that describe the resource.
     *
     * Generated from protobuf field <code>repeated .opentelemetry.proto.common.v1.KeyValue attributes = 1;</code>
     */
    private $attributes;
    /**
     * dropped_attributes_count is the number of dropped attributes. If the value is 0, then
     * no attributes were dropped.
     *
     * Generated from protobuf field <code>uint32 dropped_attributes_count = 2;</code>
     */
    private $dropped_attributes_count = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Opentelemetry\Proto\Common\V1\KeyValue[]|\Google\Protobuf\Internal\RepeatedField $attributes
     *           Set of labels that describe the resource.
     *     @type int $dropped_attributes_count
     *           dropped_attributes_count is the number of dropped attributes. If the value is 0, then
     *           no attributes were dropped.
     * }
     */
    public function __construct($data = null)
    {
        \GPBMetadata\Opentelemetry\Proto\Resource\V1\Resource::initOnce();
        parent::__construct($data);
    }

    /**
     * Set of labels that describe the resource.
     *
     * Generated from protobuf field <code>repeated .opentelemetry.proto.common.v1.KeyValue attributes = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set of labels that describe the resource.
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
     * dropped_attributes_count is the number of dropped attributes. If the value is 0, then
     * no attributes were dropped.
     *
     * Generated from protobuf field <code>uint32 dropped_attributes_count = 2;</code>
     * @return int
     */
    public function getDroppedAttributesCount()
    {
        return $this->dropped_attributes_count;
    }

    /**
     * dropped_attributes_count is the number of dropped attributes. If the value is 0, then
     * no attributes were dropped.
     *
     * Generated from protobuf field <code>uint32 dropped_attributes_count = 2;</code>
     * @param int $var
     * @return $this
     */
    public function setDroppedAttributesCount($var)
    {
        GPBUtil::checkUint32($var);
        $this->dropped_attributes_count = $var;

        return $this;
    }
}
