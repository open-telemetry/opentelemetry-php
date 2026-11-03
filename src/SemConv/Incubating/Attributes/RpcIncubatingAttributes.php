<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for rpc.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/rpc/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface RpcIncubatingAttributes
{
    /**
     * The fully-qualified logical name of the method from the RPC interface perspective.
     * The method name MAY have unbounded cardinality in edge or error cases.
     *
     * Some RPC frameworks or libraries provide a fixed set of recognized methods
     * for client stubs and server implementations. Instrumentations for such
     * frameworks MUST set this attribute to the original method name only
     * when the method is recognized by the framework or library.
     *
     * When the method is not recognized, for example, when the server receives
     * a request for a method that is not predefined on the server, or when
     * instrumentation is not able to reliably detect if the method is predefined,
     * the attribute MUST be set to `_OTHER`.
     *
     * If the RPC instrumentation could end up converting valid RPC methods to
     * `_OTHER`, then it SHOULD provide a way to configure the list of recognized
     * RPC methods.
     *
     * The `rpc.method` can be different from the name of any implementing
     * method/function.
     * The `code.function.name` attribute may be used to record the fully-qualified
     * method actually executing the call on the server side, or the
     * RPC client stub method on the client side.
     *
     * @stable
     */
    public const RPC_METHOD = 'rpc.method';

    /**
     * The original name of the method used by the client.
     *
     * @stable
     */
    public const RPC_METHOD_ORIGINAL = 'rpc.method_original';

    /**
     * RPC request headers or metadata, `<key>` being the normalized header name (lowercase), the value being the header values.
     *
     * Instrumentations SHOULD require an explicit configuration of which headers are to be captured.
     * Including all request headers can be a security risk - explicit configuration helps avoid leaking sensitive information.
     *
     * For example, a header `my-custom-key` with value `["1.2.3.4", "1.2.3.5"]` SHOULD be recorded as
     * the `rpc.request.header.my-custom-key` attribute with value `["1.2.3.4", "1.2.3.5"]`
     *
     * The attribute value MUST consist of either multiple header values as an array of
     * strings or a single-item array containing a possibly comma-concatenated string,
     * depending on the way the RPC library provides access to headers.
     *
     * Values that the RPC system exposes as binary data, or that are otherwise not valid
     * UTF-8 strings, SHOULD be base64-encoded when recorded.
     *
     * @experimental
     */
    public const RPC_REQUEST_HEADER = 'rpc.request.header';

    /**
     * RPC response headers or metadata sent before the response payload, `<key>` being the normalized header name (lowercase), the value being the header values.
     *
     * Instrumentations SHOULD require an explicit configuration of which headers are to be captured.
     * Including all response headers can be a security risk - explicit configuration helps avoid leaking sensitive information.
     *
     * For example, a header `my-custom-key` with value `["attribute_value"]` SHOULD be recorded as
     * the `rpc.response.header.my-custom-key` attribute with value `["attribute_value"]`
     *
     * The attribute value MUST consist of either multiple header values as an array of
     * strings or a single-item array containing a possibly comma-concatenated string,
     * depending on the way the RPC library provides access to headers.
     *
     * Values that the RPC system exposes as binary data, or that are otherwise not valid
     * UTF-8 strings, SHOULD be base64-encoded when recorded.
     *
     * @experimental
     */
    public const RPC_RESPONSE_HEADER = 'rpc.response.header';

    /**
     * RPC response trailers or metadata sent after the response payload, `<key>` being the normalized trailer name (lowercase), the value being the trailer values.
     *
     * Instrumentations SHOULD require an explicit configuration of which trailers are to be captured.
     * Including all response trailers can be a security risk - explicit configuration helps avoid leaking sensitive information.
     *
     * For example, a trailer `my-custom-key` with value `["attribute_value"]` SHOULD be recorded as
     * the `rpc.response.trailer.my-custom-key` attribute with value `["attribute_value"]`
     *
     * The attribute value MUST consist of either multiple trailer values as an array of
     * strings or a single-item array containing a possibly comma-concatenated string,
     * depending on the way the RPC library provides access to trailers.
     *
     * Values that the RPC system exposes as binary data, or that are otherwise not valid
     * UTF-8 strings, SHOULD be base64-encoded when recorded.
     *
     * @experimental
     */
    public const RPC_RESPONSE_TRAILER = 'rpc.response.trailer';

    /**
     * Status code of the RPC returned by the RPC server or generated by the client
     * Usually it represents an error code, but may also represent partial success, warning, or differentiate between various types of successful outcomes.
     * The value MAY use a standardized string representation of a numeric status code, if one exists, instead of the numeric code. The value MAY be a synthetic status provided by the client library rather than returned by the remote service.
     * Semantic conventions for individual RPC frameworks SHOULD document what `rpc.status_code` means in the context of that system and which values are considered to represent errors.
     *
     * @stable
     */
    public const RPC_STATUS_CODE = 'rpc.status_code';

    /**
     * The Remote Procedure Call (RPC) system.
     * The client and server RPC systems may differ for the same RPC interaction. For example, a client may use Apache Dubbo or Connect RPC to communicate with a server that uses gRPC since both protocols provide compatibility with gRPC.
     *
     * @stable
     */
    public const RPC_SYSTEM_NAME = 'rpc.system.name';

    /**
     * [gRPC](https://grpc.io/)
     * @stable
     */
    public const RPC_SYSTEM_NAME_VALUE_GRPC = 'grpc';

    /**
     * [Apache Dubbo](https://dubbo.apache.org/)
     * @stable
     */
    public const RPC_SYSTEM_NAME_VALUE_DUBBO = 'dubbo';

    /**
     * [Connect RPC](https://connectrpc.com/)
     * @stable
     */
    public const RPC_SYSTEM_NAME_VALUE_CONNECTRPC = 'connectrpc';

    /**
     * [JSON-RPC](https://www.jsonrpc.org/)
     * @stable
     */
    public const RPC_SYSTEM_NAME_VALUE_JSONRPC = 'jsonrpc';

}
