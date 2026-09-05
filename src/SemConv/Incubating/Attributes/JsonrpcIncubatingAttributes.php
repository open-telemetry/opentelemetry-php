<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for jsonrpc.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/jsonrpc/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface JsonrpcIncubatingAttributes
{
    /**
     * Protocol version, as specified in the `jsonrpc` property of the request and its corresponding response.
     *
     * @experimental
     */
    public const JSONRPC_PROTOCOL_VERSION = 'jsonrpc.protocol.version';

    /**
     * A string representation of the `id` property of the request and its corresponding response.
     *
     * Under the [JSON-RPC specification](https://www.jsonrpc.org/specification), the `id` property may be a string, number, null, or omitted entirely. When omitted, the request is treated as a notification. Using `null` is not equivalent to omitting the `id`, but it is discouraged.
     * Instrumentations SHOULD NOT capture this attribute when the `id` is `null` or omitted.
     *
     * @experimental
     */
    public const JSONRPC_REQUEST_ID = 'jsonrpc.request.id';

}
