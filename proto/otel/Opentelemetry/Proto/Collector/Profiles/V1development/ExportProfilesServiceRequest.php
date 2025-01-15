<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: opentelemetry/proto/collector/profiles/v1development/profiles_service.proto

namespace Opentelemetry\Proto\Collector\Profiles\V1development;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>opentelemetry.proto.collector.profiles.v1development.ExportProfilesServiceRequest</code>
 */
class ExportProfilesServiceRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * An array of ResourceProfiles.
     * For data coming from a single resource this array will typically contain one
     * element. Intermediary nodes (such as OpenTelemetry Collector) that receive
     * data from multiple origins typically batch the data before forwarding further and
     * in that case this array will contain multiple elements.
     *
     * Generated from protobuf field <code>repeated .opentelemetry.proto.profiles.v1development.ResourceProfiles resource_profiles = 1;</code>
     */
    private $resource_profiles;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Opentelemetry\Proto\Profiles\V1development\ResourceProfiles[]|\Google\Protobuf\Internal\RepeatedField $resource_profiles
     *           An array of ResourceProfiles.
     *           For data coming from a single resource this array will typically contain one
     *           element. Intermediary nodes (such as OpenTelemetry Collector) that receive
     *           data from multiple origins typically batch the data before forwarding further and
     *           in that case this array will contain multiple elements.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Opentelemetry\Proto\Collector\Profiles\V1Development\ProfilesService::initOnce();
        parent::__construct($data);
    }

    /**
     * An array of ResourceProfiles.
     * For data coming from a single resource this array will typically contain one
     * element. Intermediary nodes (such as OpenTelemetry Collector) that receive
     * data from multiple origins typically batch the data before forwarding further and
     * in that case this array will contain multiple elements.
     *
     * Generated from protobuf field <code>repeated .opentelemetry.proto.profiles.v1development.ResourceProfiles resource_profiles = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getResourceProfiles()
    {
        return $this->resource_profiles;
    }

    /**
     * An array of ResourceProfiles.
     * For data coming from a single resource this array will typically contain one
     * element. Intermediary nodes (such as OpenTelemetry Collector) that receive
     * data from multiple origins typically batch the data before forwarding further and
     * in that case this array will contain multiple elements.
     *
     * Generated from protobuf field <code>repeated .opentelemetry.proto.profiles.v1development.ResourceProfiles resource_profiles = 1;</code>
     * @param \Opentelemetry\Proto\Profiles\V1development\ResourceProfiles[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setResourceProfiles($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Opentelemetry\Proto\Profiles\V1development\ResourceProfiles::class);
        $this->resource_profiles = $arr;

        return $this;
    }

}

