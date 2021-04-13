<?php

declare(strict_types=1);
// Generated by the protocol buffer compiler.  DO NOT EDIT!
// source: opentelemetry/proto/trace/v1/trace.proto

namespace Opentelemetry\Proto\Trace\V1\Status;

/**
 * For the semantics of status codes see
 * https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/trace/api.md#set-status
 *
 * Protobuf type <code>opentelemetry.proto.trace.v1.Status.StatusCode</code>
 */
class StatusCode
{
    /**
     * The default status.
     *
     * Generated from protobuf enum <code>STATUS_CODE_UNSET = 0;</code>
     */
    const STATUS_CODE_UNSET = 0;
    /**
     * The Span has been validated by an Application developers or Operator to have
     * completed successfully.
     *
     * Generated from protobuf enum <code>STATUS_CODE_OK = 1;</code>
     */
    const STATUS_CODE_OK = 1;
    /**
     * The Span contains an error.
     *
     * Generated from protobuf enum <code>STATUS_CODE_ERROR = 2;</code>
     */
    const STATUS_CODE_ERROR = 2;
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(StatusCode::class, \Opentelemetry\Proto\Trace\V1\Status_StatusCode::class);
