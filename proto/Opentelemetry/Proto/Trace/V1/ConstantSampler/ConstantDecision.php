<?php

declare(strict_types=1);
// Generated by the protocol buffer compiler.  DO NOT EDIT!
// source: opentelemetry/proto/trace/v1/trace_config.proto

namespace Opentelemetry\Proto\Trace\V1\ConstantSampler;

/**
 * How spans should be sampled:
 * - Always off
 * - Always on
 * - Always follow the parent Span's decision (off if no parent).
 *
 * Protobuf type <code>opentelemetry.proto.trace.v1.ConstantSampler.ConstantDecision</code>
 */
class ConstantDecision
{
    /**
     * Generated from protobuf enum <code>ALWAYS_OFF = 0;</code>
     */
    const ALWAYS_OFF = 0;
    /**
     * Generated from protobuf enum <code>ALWAYS_ON = 1;</code>
     */
    const ALWAYS_ON = 1;
    /**
     * Generated from protobuf enum <code>ALWAYS_PARENT = 2;</code>
     */
    const ALWAYS_PARENT = 2;
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(ConstantDecision::class, \Opentelemetry\Proto\Trace\V1\ConstantSampler_ConstantDecision::class);
