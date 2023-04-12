<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-convention/templates/Attributes.php.j2

declare(strict_types=1);

namespace OpenTelemetry\SemConv;

interface MetricAttributes
{
    /**
     * The URL of the OpenTelemetry schema for these keys and values.
     */
    public const SCHEMA_URL = 'https://opentelemetry.io/schemas/1.19.0';

    /**
     * The URI scheme identifying the used protocol.
     *
     * @example http
     * @example https
     */
    public const HTTP_SCHEME = 'http.scheme';

    /**
     * The matched route (path template in the format used by the respective server framework). See note below.
     *
     * MUST NOT be populated when this is not supported by the HTTP server framework as the route attribute should have low-cardinality and the URI path can NOT substitute it.
     * SHOULD include the application root if there is one.
     *
     * @example /users/:userID?
     * @example {controller}/{action}/{id?}
     */
    public const HTTP_ROUTE = 'http.route';

    /**
     * Name of the local HTTP server that received the request.
     *
     * Determined by using the first of the following that applies<ul>
     * <li>The primary server name of the matched virtual host. MUST only
     * include host identifier.</li>
     * <li>Host identifier of the request target
     * if it's sent in absolute-form.</li>
     * <li>Host identifier of the `Host` header</li>
     * </ul>
     * SHOULD NOT be set if only IP address is available and capturing name would require a reverse DNS lookup.
     *
     * @example localhost
     */
    public const NET_HOST_NAME = 'net.host.name';

    /**
     * Port of the local HTTP server that received the request.
     *
     * Determined by using the first of the following that applies<ul>
     * <li>Port identifier of the primary server host of the matched virtual host.</li>
     * <li>Port identifier of the request target
     * if it's sent in absolute-form.</li>
     * <li>Port identifier of the `Host` header</li>
     * </ul>
     *
     * @example 8080
     */
    public const NET_HOST_PORT = 'net.host.port';

    /**
     * HTTP request method.
     *
     * @example GET
     * @example POST
     * @example HEAD
     */
    public const HTTP_METHOD = 'http.method';

    /**
     * HTTP response status code.
     *
     * @example 200
     */
    public const HTTP_STATUS_CODE = 'http.status_code';

    /**
     * Kind of HTTP protocol used.
     */
    public const HTTP_FLAVOR = 'http.flavor';

    /**
     * Host identifier of the &quot;URI origin&quot; HTTP request is sent to.
     *
     * Determined by using the first of the following that applies<ul>
     * <li>Host identifier of the request target
     * if it's sent in absolute-form</li>
     * <li>Host identifier of the `Host` header</li>
     * </ul>
     * SHOULD NOT be set if capturing it would require an extra DNS lookup.
     *
     * @example example.com
     */
    public const NET_PEER_NAME = 'net.peer.name';

    /**
     * Port identifier of the &quot;URI origin&quot; HTTP request is sent to.
     *
     * When request target is absolute URI, `net.peer.name` MUST match URI port identifier, otherwise it MUST match `Host` header port identifier.
     *
     * @example 80
     * @example 8080
     * @example 443
     */
    public const NET_PEER_PORT = 'net.peer.port';

    /**
     * Remote socket peer address: IPv4 or IPv6 for internet protocols, path for local communication, etc.
     *
     * @example 127.0.0.1
     * @example /tmp/mysql.sock
     */
    public const NET_SOCK_PEER_ADDR = 'net.sock.peer.addr';
}
