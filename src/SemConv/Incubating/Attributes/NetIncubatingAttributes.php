<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for net.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/net/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface NetIncubatingAttributes
{
    /**
     * Deprecated, use `network.local.address`.
     *
     * @experimental
     * @deprecated Replaced by `network.local.address`.
     */
    public const NET_HOST_IP = 'net.host.ip';

    /**
     * Deprecated, use `server.address`.
     *
     * @experimental
     * @deprecated Replaced by `server.address`.
     */
    public const NET_HOST_NAME = 'net.host.name';

    /**
     * Deprecated, use `server.port`.
     *
     * @experimental
     * @deprecated Replaced by `server.port`.
     */
    public const NET_HOST_PORT = 'net.host.port';

    /**
     * Deprecated, use `network.peer.address`.
     *
     * @experimental
     * @deprecated Replaced by `network.peer.address`.
     */
    public const NET_PEER_IP = 'net.peer.ip';

    /**
     * Deprecated, use `server.address` on client spans and `client.address` on server spans.
     *
     * @experimental
     * @deprecated Replaced by `server.address` on client spans and `client.address` on server spans.
     */
    public const NET_PEER_NAME = 'net.peer.name';

    /**
     * Deprecated, use `server.port` on client spans and `client.port` on server spans.
     *
     * @experimental
     * @deprecated Replaced by `server.port` on client spans and `client.port` on server spans.
     */
    public const NET_PEER_PORT = 'net.peer.port';

    /**
     * Deprecated, use `network.protocol.name`.
     *
     * @experimental
     * @deprecated Replaced by `network.protocol.name`.
     */
    public const NET_PROTOCOL_NAME = 'net.protocol.name';

    /**
     * Deprecated, use `network.protocol.version`.
     *
     * @experimental
     * @deprecated Replaced by `network.protocol.version`.
     */
    public const NET_PROTOCOL_VERSION = 'net.protocol.version';

    /**
     * Deprecated, use `network.transport` and `network.type`.
     *
     * @experimental
     * @deprecated Split to `network.transport` and `network.type`.
     */
    public const NET_SOCK_FAMILY = 'net.sock.family';

    /**
     * IPv4 address
     * @experimental
     * @deprecated Split to `network.transport` and `network.type`.
     */
    public const NET_SOCK_FAMILY_VALUE_INET = 'inet';

    /**
     * IPv6 address
     * @experimental
     * @deprecated Split to `network.transport` and `network.type`.
     */
    public const NET_SOCK_FAMILY_VALUE_INET6 = 'inet6';

    /**
     * UNIX domain socket path
     * @experimental
     * @deprecated Split to `network.transport` and `network.type`.
     */
    public const NET_SOCK_FAMILY_VALUE_UNIX = 'unix';

    /**
     * Deprecated, use `network.local.address`.
     *
     * @experimental
     * @deprecated Replaced by `network.local.address`.
     */
    public const NET_SOCK_HOST_ADDR = 'net.sock.host.addr';

    /**
     * Deprecated, use `network.local.port`.
     *
     * @experimental
     * @deprecated Replaced by `network.local.port`.
     */
    public const NET_SOCK_HOST_PORT = 'net.sock.host.port';

    /**
     * Deprecated, use `network.peer.address`.
     *
     * @experimental
     * @deprecated Replaced by `network.peer.address`.
     */
    public const NET_SOCK_PEER_ADDR = 'net.sock.peer.addr';

    /**
     * Deprecated, no replacement at this time.
     *
     * @experimental
     * @deprecated Removed. No replacement at this time.
     */
    public const NET_SOCK_PEER_NAME = 'net.sock.peer.name';

    /**
     * Deprecated, use `network.peer.port`.
     *
     * @experimental
     * @deprecated Replaced by `network.peer.port`.
     */
    public const NET_SOCK_PEER_PORT = 'net.sock.peer.port';

    /**
     * Deprecated, use `network.transport`.
     *
     * @experimental
     * @deprecated Replaced by `network.transport`.
     */
    public const NET_TRANSPORT = 'net.transport';

    /**
     * @experimental
     * @deprecated Replaced by `network.transport`.
     */
    public const NET_TRANSPORT_VALUE_IP_TCP = 'ip_tcp';

    /**
     * @experimental
     * @deprecated Replaced by `network.transport`.
     */
    public const NET_TRANSPORT_VALUE_IP_UDP = 'ip_udp';

    /**
     * Named or anonymous pipe.
     * @experimental
     * @deprecated Replaced by `network.transport`.
     */
    public const NET_TRANSPORT_VALUE_PIPE = 'pipe';

    /**
     * In-process communication.
     * @experimental
     * @deprecated Replaced by `network.transport`.
     */
    public const NET_TRANSPORT_VALUE_INPROC = 'inproc';

    /**
     * Something else (non IP-based).
     * @experimental
     * @deprecated Replaced by `network.transport`.
     */
    public const NET_TRANSPORT_VALUE_OTHER = 'other';

}
