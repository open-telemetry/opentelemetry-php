<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: opentelemetry/proto/common/v1/common.proto

namespace GPBMetadata\Opentelemetry\Proto\Common\V1;

class Common
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
          return;
        }
        $pool->internalAddGeneratedFile(
            '
�
*opentelemetry/proto/common/v1/common.protoopentelemetry.proto.common.v1"�
AnyValue
string_value (	H 

bool_value (H 
	int_value (H 
double_value (H @
array_value (2).opentelemetry.proto.common.v1.ArrayValueH C
kvlist_value (2+.opentelemetry.proto.common.v1.KeyValueListH 
bytes_value (H B
value"E

ArrayValue7
values (2\'.opentelemetry.proto.common.v1.AnyValue"G
KeyValueList7
values (2\'.opentelemetry.proto.common.v1.KeyValue"O
KeyValue
key (	6
value (2\'.opentelemetry.proto.common.v1.AnyValue"7
InstrumentationLibrary
name (	
version (	B[
 io.opentelemetry.proto.common.v1BCommonProtoPZ(go.opentelemetry.io/proto/otlp/common/v1bproto3'
        , true);

        static::$is_initialized = true;
    }
}

