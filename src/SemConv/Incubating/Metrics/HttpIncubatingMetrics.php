<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Unstable\Metrics;

/**
 * Metrics for http.
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface HttpIncubatingMetrics
{
    /**
     * Number of active HTTP requests.
     *
     * Instrument: updowncounter
     * Unit: {request}
     * @experimental
     */
    public const HTTP_CLIENT_ACTIVE_REQUESTS = 'http.client.active_requests';

    /**
     * The duration of the successfully established outbound HTTP connections.
     *
     * Instrument: histogram
     * Unit: s
     * @experimental
     */
    public const HTTP_CLIENT_CONNECTION_DURATION = 'http.client.connection.duration';

    /**
     * Number of outbound HTTP connections that are currently active or idle on the client.
     *
     * Instrument: updowncounter
     * Unit: {connection}
     * @experimental
     */
    public const HTTP_CLIENT_OPEN_CONNECTIONS = 'http.client.open_connections';

    /**
     * Size of HTTP client request bodies.
     * The size of the request payload body in bytes. This is the number of bytes transferred excluding headers and is often, but not always, present as the [Content-Length](https://www.rfc-editor.org/rfc/rfc9110.html#field.content-length) header. For requests using transport encoding, this should be the compressed size.
     *
     * Instrument: histogram
     * Unit: By
     * @experimental
     */
    public const HTTP_CLIENT_REQUEST_BODY_SIZE = 'http.client.request.body.size';

    /**
     * Duration of HTTP client requests.
     *
     * Instrument: histogram
     * Unit: s
     * @stable
     */
    public const HTTP_CLIENT_REQUEST_DURATION = 'http.client.request.duration';

    /**
     * Size of HTTP client response bodies.
     * The size of the response payload body in bytes. This is the number of bytes transferred excluding headers and is often, but not always, present as the [Content-Length](https://www.rfc-editor.org/rfc/rfc9110.html#field.content-length) header. For requests using transport encoding, this should be the compressed size.
     *
     * Instrument: histogram
     * Unit: By
     * @experimental
     */
    public const HTTP_CLIENT_RESPONSE_BODY_SIZE = 'http.client.response.body.size';

    /**
     * Number of active HTTP server requests.
     *
     * Instrument: updowncounter
     * Unit: {request}
     * @experimental
     */
    public const HTTP_SERVER_ACTIVE_REQUESTS = 'http.server.active_requests';

    /**
     * Size of HTTP server request bodies.
     * The size of the request payload body in bytes. This is the number of bytes transferred excluding headers and is often, but not always, present as the [Content-Length](https://www.rfc-editor.org/rfc/rfc9110.html#field.content-length) header. For requests using transport encoding, this should be the compressed size.
     *
     * Instrument: histogram
     * Unit: By
     * @experimental
     */
    public const HTTP_SERVER_REQUEST_BODY_SIZE = 'http.server.request.body.size';

    /**
     * Duration of HTTP server requests.
     *
     * Instrument: histogram
     * Unit: s
     * @stable
     */
    public const HTTP_SERVER_REQUEST_DURATION = 'http.server.request.duration';

    /**
     * Size of HTTP server response bodies.
     * The size of the response payload body in bytes. This is the number of bytes transferred excluding headers and is often, but not always, present as the [Content-Length](https://www.rfc-editor.org/rfc/rfc9110.html#field.content-length) header. For requests using transport encoding, this should be the compressed size.
     *
     * Instrument: histogram
     * Unit: By
     * @experimental
     */
    public const HTTP_SERVER_RESPONSE_BODY_SIZE = 'http.server.response.body.size';

}
