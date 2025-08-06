<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Attributes;

/**
 * Semantic attributes and corresponding values for network.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/network/
 */
interface NetworkAttributes
{
    /**
     * Local address of the network connection - IP address or Unix domain socket name.
     *
     * @stable
     */
    public const NETWORK_LOCAL_ADDRESS = 'network.local.address';

    /**
     * Local port number of the network connection.
     *
     * @stable
     */
    public const NETWORK_LOCAL_PORT = 'network.local.port';

    /**
     * Peer address of the network connection - IP address or Unix domain socket name.
     *
     * @stable
     */
    public const NETWORK_PEER_ADDRESS = 'network.peer.address';

    /**
     * Peer port number of the network connection.
     *
     * @stable
     */
    public const NETWORK_PEER_PORT = 'network.peer.port';

    /**
     * [OSI application layer](https://wikipedia.org/wiki/Application_layer) or non-OSI equivalent.
     * The value SHOULD be normalized to lowercase.
     * @stable
     */
    public const NETWORK_PROTOCOL_NAME = 'network.protocol.name';

    /**
     * The actual version of the protocol used for network communication.
     * If protocol version is subject to negotiation (for example using [ALPN](https://www.rfc-editor.org/rfc/rfc7301.html)), this attribute SHOULD be set to the negotiated version. If the actual protocol version is not known, this attribute SHOULD NOT be set.
     *
     * @stable
     */
    public const NETWORK_PROTOCOL_VERSION = 'network.protocol.version';

    /**
     * [OSI transport layer](https://wikipedia.org/wiki/Transport_layer) or [inter-process communication method](https://wikipedia.org/wiki/Inter-process_communication).
     *
     * The value SHOULD be normalized to lowercase.
     *
     * Consider always setting the transport when setting a port number, since
     * a port number is ambiguous without knowing the transport. For example
     * different processes could be listening on TCP port 12345 and UDP port 12345.
     *
     * @stable
     */
    public const NETWORK_TRANSPORT = 'network.transport';

    /**
     * TCP
     * @stable
     */
    public const NETWORK_TRANSPORT_VALUE_TCP = 'tcp';

    /**
     * UDP
     * @stable
     */
    public const NETWORK_TRANSPORT_VALUE_UDP = 'udp';

    /**
     * Named or anonymous pipe.
     * @stable
     */
    public const NETWORK_TRANSPORT_VALUE_PIPE = 'pipe';

    /**
     * Unix domain socket
     * @stable
     */
    public const NETWORK_TRANSPORT_VALUE_UNIX = 'unix';

    /**
     * QUIC
     * @stable
     */
    public const NETWORK_TRANSPORT_VALUE_QUIC = 'quic';

    /**
     * [OSI network layer](https://wikipedia.org/wiki/Network_layer) or non-OSI equivalent.
     * The value SHOULD be normalized to lowercase.
     * @stable
     */
    public const NETWORK_TYPE = 'network.type';

    /**
     * IPv4
     * @stable
     */
    public const NETWORK_TYPE_VALUE_IPV4 = 'ipv4';

    /**
     * IPv6
     * @stable
     */
    public const NETWORK_TYPE_VALUE_IPV6 = 'ipv6';

}
