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
     * Deprecated, use `rpc.status_code` attribute instead.
     *
     * @experimental
     * @deprecated Replaced by `rpc.status_code`.
     */
    public const RPC_CONNECT_RPC_ERROR_CODE = 'rpc.connect_rpc.error_code';

    /**
     * @experimental
     * @deprecated Replaced by `rpc.status_code`.
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_VALUE_CANCELLED = 'cancelled';

    /**
     * @experimental
     * @deprecated Replaced by `rpc.status_code`.
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_VALUE_UNKNOWN = 'unknown';

    /**
     * @experimental
     * @deprecated Replaced by `rpc.status_code`.
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_VALUE_INVALID_ARGUMENT = 'invalid_argument';

    /**
     * @experimental
     * @deprecated Replaced by `rpc.status_code`.
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_VALUE_DEADLINE_EXCEEDED = 'deadline_exceeded';

    /**
     * @experimental
     * @deprecated Replaced by `rpc.status_code`.
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_VALUE_NOT_FOUND = 'not_found';

    /**
     * @experimental
     * @deprecated Replaced by `rpc.status_code`.
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_VALUE_ALREADY_EXISTS = 'already_exists';

    /**
     * @experimental
     * @deprecated Replaced by `rpc.status_code`.
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_VALUE_PERMISSION_DENIED = 'permission_denied';

    /**
     * @experimental
     * @deprecated Replaced by `rpc.status_code`.
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_VALUE_RESOURCE_EXHAUSTED = 'resource_exhausted';

    /**
     * @experimental
     * @deprecated Replaced by `rpc.status_code`.
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_VALUE_FAILED_PRECONDITION = 'failed_precondition';

    /**
     * @experimental
     * @deprecated Replaced by `rpc.status_code`.
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_VALUE_ABORTED = 'aborted';

    /**
     * @experimental
     * @deprecated Replaced by `rpc.status_code`.
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_VALUE_OUT_OF_RANGE = 'out_of_range';

    /**
     * @experimental
     * @deprecated Replaced by `rpc.status_code`.
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_VALUE_UNIMPLEMENTED = 'unimplemented';

    /**
     * @experimental
     * @deprecated Replaced by `rpc.status_code`.
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_VALUE_INTERNAL = 'internal';

    /**
     * @experimental
     * @deprecated Replaced by `rpc.status_code`.
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_VALUE_UNAVAILABLE = 'unavailable';

    /**
     * @experimental
     * @deprecated Replaced by `rpc.status_code`.
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_VALUE_DATA_LOSS = 'data_loss';

    /**
     * @experimental
     * @deprecated Replaced by `rpc.status_code`.
     */
    public const RPC_CONNECT_RPC_ERROR_CODE_VALUE_UNAUTHENTICATED = 'unauthenticated';

    /**
     * Deprecated, use `rpc.request.header` instead.
     *
     * @experimental
     * @deprecated Replaced by `rpc.request.header`.
     */
    public const RPC_CONNECT_RPC_REQUEST_METADATA = 'rpc.connect_rpc.request.metadata';

    /**
     * Deprecated, use `rpc.response.header` and `rpc.response.trailer`.
     *
     * @experimental
     * @deprecated Split to `rpc.response.header` for metadata sent before the response payload and `rpc.response.trailer` for metadata sent after it.
     */
    public const RPC_CONNECT_RPC_RESPONSE_METADATA = 'rpc.connect_rpc.response.metadata';

    /**
     * Deprecated, use `rpc.request.header` instead.
     *
     * @experimental
     * @deprecated Replaced by `rpc.request.header`.
     */
    public const RPC_GRPC_REQUEST_METADATA = 'rpc.grpc.request.metadata';

    /**
     * Deprecated, use `rpc.response.header` and `rpc.response.trailer`.
     *
     * @experimental
     * @deprecated Split to `rpc.response.header` for metadata sent before the response payload and `rpc.response.trailer` for metadata sent after it.
     */
    public const RPC_GRPC_RESPONSE_METADATA = 'rpc.grpc.response.metadata';

    /**
     * Deprecated, use string representation on the `rpc.status_code` attribute instead.
     *
     * @experimental
     * @deprecated Use string representation of the gRPC status code on the `rpc.status_code` attribute.
     */
    public const RPC_GRPC_STATUS_CODE = 'rpc.grpc.status_code';

    /**
     * OK
     * @experimental
     * @deprecated Use string representation of the gRPC status code on the `rpc.status_code` attribute.
     */
    public const RPC_GRPC_STATUS_CODE_VALUE_OK = 0;

    /**
     * CANCELLED
     * @experimental
     * @deprecated Use string representation of the gRPC status code on the `rpc.status_code` attribute.
     */
    public const RPC_GRPC_STATUS_CODE_VALUE_CANCELLED = 1;

    /**
     * UNKNOWN
     * @experimental
     * @deprecated Use string representation of the gRPC status code on the `rpc.status_code` attribute.
     */
    public const RPC_GRPC_STATUS_CODE_VALUE_UNKNOWN = 2;

    /**
     * INVALID_ARGUMENT
     * @experimental
     * @deprecated Use string representation of the gRPC status code on the `rpc.status_code` attribute.
     */
    public const RPC_GRPC_STATUS_CODE_VALUE_INVALID_ARGUMENT = 3;

    /**
     * DEADLINE_EXCEEDED
     * @experimental
     * @deprecated Use string representation of the gRPC status code on the `rpc.status_code` attribute.
     */
    public const RPC_GRPC_STATUS_CODE_VALUE_DEADLINE_EXCEEDED = 4;

    /**
     * NOT_FOUND
     * @experimental
     * @deprecated Use string representation of the gRPC status code on the `rpc.status_code` attribute.
     */
    public const RPC_GRPC_STATUS_CODE_VALUE_NOT_FOUND = 5;

    /**
     * ALREADY_EXISTS
     * @experimental
     * @deprecated Use string representation of the gRPC status code on the `rpc.status_code` attribute.
     */
    public const RPC_GRPC_STATUS_CODE_VALUE_ALREADY_EXISTS = 6;

    /**
     * PERMISSION_DENIED
     * @experimental
     * @deprecated Use string representation of the gRPC status code on the `rpc.status_code` attribute.
     */
    public const RPC_GRPC_STATUS_CODE_VALUE_PERMISSION_DENIED = 7;

    /**
     * RESOURCE_EXHAUSTED
     * @experimental
     * @deprecated Use string representation of the gRPC status code on the `rpc.status_code` attribute.
     */
    public const RPC_GRPC_STATUS_CODE_VALUE_RESOURCE_EXHAUSTED = 8;

    /**
     * FAILED_PRECONDITION
     * @experimental
     * @deprecated Use string representation of the gRPC status code on the `rpc.status_code` attribute.
     */
    public const RPC_GRPC_STATUS_CODE_VALUE_FAILED_PRECONDITION = 9;

    /**
     * ABORTED
     * @experimental
     * @deprecated Use string representation of the gRPC status code on the `rpc.status_code` attribute.
     */
    public const RPC_GRPC_STATUS_CODE_VALUE_ABORTED = 10;

    /**
     * OUT_OF_RANGE
     * @experimental
     * @deprecated Use string representation of the gRPC status code on the `rpc.status_code` attribute.
     */
    public const RPC_GRPC_STATUS_CODE_VALUE_OUT_OF_RANGE = 11;

    /**
     * UNIMPLEMENTED
     * @experimental
     * @deprecated Use string representation of the gRPC status code on the `rpc.status_code` attribute.
     */
    public const RPC_GRPC_STATUS_CODE_VALUE_UNIMPLEMENTED = 12;

    /**
     * INTERNAL
     * @experimental
     * @deprecated Use string representation of the gRPC status code on the `rpc.status_code` attribute.
     */
    public const RPC_GRPC_STATUS_CODE_VALUE_INTERNAL = 13;

    /**
     * UNAVAILABLE
     * @experimental
     * @deprecated Use string representation of the gRPC status code on the `rpc.status_code` attribute.
     */
    public const RPC_GRPC_STATUS_CODE_VALUE_UNAVAILABLE = 14;

    /**
     * DATA_LOSS
     * @experimental
     * @deprecated Use string representation of the gRPC status code on the `rpc.status_code` attribute.
     */
    public const RPC_GRPC_STATUS_CODE_VALUE_DATA_LOSS = 15;

    /**
     * UNAUTHENTICATED
     * @experimental
     * @deprecated Use string representation of the gRPC status code on the `rpc.status_code` attribute.
     */
    public const RPC_GRPC_STATUS_CODE_VALUE_UNAUTHENTICATED = 16;

    /**
     * Deprecated, use string representation on the `rpc.status_code` attribute instead.
     *
     * @experimental
     * @deprecated Use string representation of the error code on the `rpc.status_code` attribute.
     */
    public const RPC_JSONRPC_ERROR_CODE = 'rpc.jsonrpc.error_code';

    /**
     * Deprecated, use the span status description when reporting JSON-RPC spans.
     *
     * @experimental
     * @deprecated Use the span status description when reporting JSON-RPC spans.
     */
    public const RPC_JSONRPC_ERROR_MESSAGE = 'rpc.jsonrpc.error_message';

    /**
     * Deprecated, use `jsonrpc.request.id` instead.
     *
     * @experimental
     * @deprecated Replaced by `jsonrpc.request.id`.
     */
    public const RPC_JSONRPC_REQUEST_ID = 'rpc.jsonrpc.request_id';

    /**
     * Deprecated, use `jsonrpc.protocol.version` instead.
     *
     * @experimental
     * @deprecated Replaced by `jsonrpc.protocol.version`.
     */
    public const RPC_JSONRPC_VERSION = 'rpc.jsonrpc.version';

    /**
     * Compressed size of the message in bytes.
     *
     * @experimental
     * @deprecated Deprecated, no replacement at this time.
     */
    public const RPC_MESSAGE_COMPRESSED_SIZE = 'rpc.message.compressed_size';

    /**
     * MUST be calculated as two different counters starting from `1` one for sent messages and one for received message.
     * This way we guarantee that the values will be consistent between different implementations.
     * @experimental
     * @deprecated Deprecated, no replacement at this time.
     */
    public const RPC_MESSAGE_ID = 'rpc.message.id';

    /**
     * Whether this is a received or sent message.
     *
     * @experimental
     * @deprecated Deprecated, no replacement at this time.
     */
    public const RPC_MESSAGE_TYPE = 'rpc.message.type';

    /**
     * @experimental
     * @deprecated Deprecated, no replacement at this time.
     */
    public const RPC_MESSAGE_TYPE_VALUE_SENT = 'SENT';

    /**
     * @experimental
     * @deprecated Deprecated, no replacement at this time.
     */
    public const RPC_MESSAGE_TYPE_VALUE_RECEIVED = 'RECEIVED';

    /**
     * Uncompressed size of the message in bytes.
     *
     * @experimental
     * @deprecated Deprecated, no replacement at this time.
     */
    public const RPC_MESSAGE_UNCOMPRESSED_SIZE = 'rpc.message.uncompressed_size';

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
     * Deprecated, use `rpc.request.header` instead.
     *
     * @experimental
     * @deprecated Replaced by `rpc.request.header`.
     */
    public const RPC_REQUEST_METADATA = 'rpc.request.metadata';

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
     * Deprecated, use `rpc.response.header` and `rpc.response.trailer`.
     *
     * @experimental
     * @deprecated Split to `rpc.response.header` for metadata sent before the response payload and `rpc.response.trailer` for metadata sent after it.
     */
    public const RPC_RESPONSE_METADATA = 'rpc.response.metadata';

    /**
     * Deprecated, use `rpc.status_code` instead.
     *
     * @experimental
     * @deprecated Replaced by `rpc.status_code`.
     */
    public const RPC_RESPONSE_STATUS_CODE = 'rpc.response.status_code';

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
     * Deprecated, use fully-qualified `rpc.method` instead.
     *
     * @experimental
     * @deprecated Value should be included in `rpc.method` which is expected to be a fully-qualified name.
     */
    public const RPC_SERVICE = 'rpc.service';

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
     * Deprecated, use `rpc.system.name` attribute instead.
     *
     * @experimental
     * @deprecated Replaced by `rpc.system.name`.
     */
    public const RPC_SYSTEM = 'rpc.system';

    /**
     * gRPC
     * @experimental
     * @deprecated Replaced by `rpc.system.name`.
     */
    public const RPC_SYSTEM_VALUE_GRPC = 'grpc';

    /**
     * Java RMI
     * @experimental
     * @deprecated Replaced by `rpc.system.name`.
     */
    public const RPC_SYSTEM_VALUE_JAVA_RMI = 'java_rmi';

    /**
     * .NET WCF
     * @experimental
     * @deprecated Replaced by `rpc.system.name`.
     */
    public const RPC_SYSTEM_VALUE_DOTNET_WCF = 'dotnet_wcf';

    /**
     * Apache Dubbo
     * @experimental
     * @deprecated Replaced by `rpc.system.name`.
     */
    public const RPC_SYSTEM_VALUE_APACHE_DUBBO = 'apache_dubbo';

    /**
     * Connect RPC
     * @experimental
     * @deprecated Replaced by `rpc.system.name`.
     */
    public const RPC_SYSTEM_VALUE_CONNECT_RPC = 'connect_rpc';

    /**
     * [ONC RPC (Sun RPC)](https://datatracker.ietf.org/doc/html/rfc5531)
     * @experimental
     * @deprecated Replaced by `rpc.system.name`.
     */
    public const RPC_SYSTEM_VALUE_ONC_RPC = 'onc_rpc';

    /**
     * JSON-RPC
     * @experimental
     * @deprecated Replaced by `rpc.system.name`.
     */
    public const RPC_SYSTEM_VALUE_JSONRPC = 'jsonrpc';

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
