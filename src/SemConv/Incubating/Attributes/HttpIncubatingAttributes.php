<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for http.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/http/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface HttpIncubatingAttributes
{
    /**
     * State of the HTTP connection in the HTTP connection pool.
     *
     * @experimental
     */
    public const HTTP_CONNECTION_STATE = 'http.connection.state';

    /**
     * active state.
     * @experimental
     */
    public const HTTP_CONNECTION_STATE_VALUE_ACTIVE = 'active';

    /**
     * idle state.
     * @experimental
     */
    public const HTTP_CONNECTION_STATE_VALUE_IDLE = 'idle';

    /**
     * The size of the request payload body in bytes. This is the number of bytes transferred excluding headers and is often, but not always, present as the [Content-Length](https://www.rfc-editor.org/rfc/rfc9110.html#field.content-length) header. For requests using transport encoding, this should be the compressed size.
     *
     * @experimental
     */
    public const HTTP_REQUEST_BODY_SIZE = 'http.request.body.size';

    /**
     * HTTP request headers, `<key>` being the normalized HTTP Header name (lowercase), the value being the header values.
     *
     * Instrumentations SHOULD require an explicit configuration of which headers are to be captured.
     * Including all request headers can be a security risk - explicit configuration helps avoid leaking sensitive information.
     *
     * The `User-Agent` header is already captured in the `user_agent.original` attribute.
     * Users MAY explicitly configure instrumentations to capture them even though it is not recommended.
     *
     * The attribute value MUST consist of either multiple header values as an array of strings
     * or a single-item array containing a possibly comma-concatenated string, depending on the way
     * the HTTP library provides access to headers.
     *
     * Examples:
     *
     * - A header `Content-Type: application/json` SHOULD be recorded as the `http.request.header.content-type`
     *   attribute with value `["application/json"]`.
     * - A header `X-Forwarded-For: 1.2.3.4, 1.2.3.5` SHOULD be recorded as the `http.request.header.x-forwarded-for`
     *   attribute with value `["1.2.3.4", "1.2.3.5"]` or `["1.2.3.4, 1.2.3.5"]` depending on the HTTP library.
     *
     * @stable
     */
    public const HTTP_REQUEST_HEADER = 'http.request.header';

    /**
     * HTTP request method.
     * HTTP request method value SHOULD be "known" to the instrumentation.
     * By default, this convention defines "known" methods as the ones listed in [RFC9110](https://www.rfc-editor.org/rfc/rfc9110.html#name-methods),
     * the PATCH method defined in [RFC5789](https://www.rfc-editor.org/rfc/rfc5789.html)
     * and the QUERY method defined in [httpbis-safe-method-w-body](https://datatracker.ietf.org/doc/draft-ietf-httpbis-safe-method-w-body/?include_text=1).
     *
     * If the HTTP request method is not known to instrumentation, it MUST set the `http.request.method` attribute to `_OTHER`.
     *
     * If the HTTP instrumentation could end up converting valid HTTP request methods to `_OTHER`, then it MUST provide a way to override
     * the list of known HTTP methods. If this override is done via environment variable, then the environment variable MUST be named
     * OTEL_INSTRUMENTATION_HTTP_KNOWN_METHODS and support a comma-separated list of case-sensitive known HTTP methods
     * (this list MUST be a full override of the default known method, it is not a list of known methods in addition to the defaults).
     *
     * HTTP method names are case-sensitive and `http.request.method` attribute value MUST match a known HTTP method name exactly.
     * Instrumentations for specific web frameworks that consider HTTP methods to be case insensitive, SHOULD populate a canonical equivalent.
     * Tracing instrumentations that do so, MUST also set `http.request.method_original` to the original value.
     *
     * @stable
     */
    public const HTTP_REQUEST_METHOD = 'http.request.method';

    /**
     * CONNECT method.
     * @stable
     */
    public const HTTP_REQUEST_METHOD_VALUE_CONNECT = 'CONNECT';

    /**
     * DELETE method.
     * @stable
     */
    public const HTTP_REQUEST_METHOD_VALUE_DELETE = 'DELETE';

    /**
     * GET method.
     * @stable
     */
    public const HTTP_REQUEST_METHOD_VALUE_GET = 'GET';

    /**
     * HEAD method.
     * @stable
     */
    public const HTTP_REQUEST_METHOD_VALUE_HEAD = 'HEAD';

    /**
     * OPTIONS method.
     * @stable
     */
    public const HTTP_REQUEST_METHOD_VALUE_OPTIONS = 'OPTIONS';

    /**
     * PATCH method.
     * @stable
     */
    public const HTTP_REQUEST_METHOD_VALUE_PATCH = 'PATCH';

    /**
     * POST method.
     * @stable
     */
    public const HTTP_REQUEST_METHOD_VALUE_POST = 'POST';

    /**
     * PUT method.
     * @stable
     */
    public const HTTP_REQUEST_METHOD_VALUE_PUT = 'PUT';

    /**
     * TRACE method.
     * @stable
     */
    public const HTTP_REQUEST_METHOD_VALUE_TRACE = 'TRACE';

    /**
     * QUERY method.
     * @stable
     */
    public const HTTP_REQUEST_METHOD_VALUE_QUERY = 'QUERY';

    /**
     * Any HTTP method that the instrumentation has no prior knowledge of.
     * @stable
     */
    public const HTTP_REQUEST_METHOD_VALUE_OTHER = '_OTHER';

    /**
     * Original HTTP method sent by the client in the request line.
     *
     * @stable
     */
    public const HTTP_REQUEST_METHOD_ORIGINAL = 'http.request.method_original';

    /**
     * The ordinal number of request resending attempt (for any reason, including redirects).
     *
     * The resend count SHOULD be updated each time an HTTP request gets resent by the client, regardless of what was the cause of the resending (e.g. redirection, authorization failure, 503 Server Unavailable, network issues, or any other).
     *
     * @stable
     */
    public const HTTP_REQUEST_RESEND_COUNT = 'http.request.resend_count';

    /**
     * The total size of the request in bytes. This should be the total number of bytes sent over the wire, including the request line (HTTP/1.1), framing (HTTP/2 and HTTP/3), headers, and request body if any.
     *
     * @experimental
     */
    public const HTTP_REQUEST_SIZE = 'http.request.size';

    /**
     * The size of the response payload body in bytes. This is the number of bytes transferred excluding headers and is often, but not always, present as the [Content-Length](https://www.rfc-editor.org/rfc/rfc9110.html#field.content-length) header. For requests using transport encoding, this should be the compressed size.
     *
     * @experimental
     */
    public const HTTP_RESPONSE_BODY_SIZE = 'http.response.body.size';

    /**
     * HTTP response headers, `<key>` being the normalized HTTP Header name (lowercase), the value being the header values.
     *
     * Instrumentations SHOULD require an explicit configuration of which headers are to be captured.
     * Including all response headers can be a security risk - explicit configuration helps avoid leaking sensitive information.
     *
     * Users MAY explicitly configure instrumentations to capture them even though it is not recommended.
     *
     * The attribute value MUST consist of either multiple header values as an array of strings
     * or a single-item array containing a possibly comma-concatenated string, depending on the way
     * the HTTP library provides access to headers.
     *
     * Examples:
     *
     * - A header `Content-Type: application/json` header SHOULD be recorded as the `http.request.response.content-type`
     *   attribute with value `["application/json"]`.
     * - A header `My-custom-header: abc, def` header SHOULD be recorded as the `http.response.header.my-custom-header`
     *   attribute with value `["abc", "def"]` or `["abc, def"]` depending on the HTTP library.
     *
     * @stable
     */
    public const HTTP_RESPONSE_HEADER = 'http.response.header';

    /**
     * The total size of the response in bytes. This should be the total number of bytes sent over the wire, including the status line (HTTP/1.1), framing (HTTP/2 and HTTP/3), headers, and response body and trailers if any.
     *
     * @experimental
     */
    public const HTTP_RESPONSE_SIZE = 'http.response.size';

    /**
     * [HTTP response status code](https://tools.ietf.org/html/rfc7231#section-6).
     *
     * @stable
     */
    public const HTTP_RESPONSE_STATUS_CODE = 'http.response.status_code';

    /**
     * The matched route, that is, the path template in the format used by the respective server framework.
     *
     * MUST NOT be populated when this is not supported by the HTTP server framework as the route attribute should have low-cardinality and the URI path can NOT substitute it.
     * SHOULD include the [application root](/docs/http/http-spans.md#http-server-definitions) if there is one.
     *
     * @stable
     */
    public const HTTP_ROUTE = 'http.route';

}
