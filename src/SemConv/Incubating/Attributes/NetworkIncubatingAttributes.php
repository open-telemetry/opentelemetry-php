<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for network.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/network/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface NetworkIncubatingAttributes
{
    /**
     * The ISO 3166-1 alpha-2 2-character country code associated with the mobile carrier network.
     *
     * @experimental
     */
    public const NETWORK_CARRIER_ICC = 'network.carrier.icc';

    /**
     * The mobile carrier country code.
     *
     * @experimental
     */
    public const NETWORK_CARRIER_MCC = 'network.carrier.mcc';

    /**
     * The mobile carrier network code.
     *
     * @experimental
     */
    public const NETWORK_CARRIER_MNC = 'network.carrier.mnc';

    /**
     * The name of the mobile carrier.
     *
     * @experimental
     */
    public const NETWORK_CARRIER_NAME = 'network.carrier.name';

    /**
     * The state of network connection
     * Connection states are defined as part of the [rfc9293](https://datatracker.ietf.org/doc/html/rfc9293#section-3.3.2)
     * @experimental
     */
    public const NETWORK_CONNECTION_STATE = 'network.connection.state';

    /**
     * @experimental
     */
    public const NETWORK_CONNECTION_STATE_VALUE_CLOSED = 'closed';

    /**
     * @experimental
     */
    public const NETWORK_CONNECTION_STATE_VALUE_CLOSE_WAIT = 'close_wait';

    /**
     * @experimental
     */
    public const NETWORK_CONNECTION_STATE_VALUE_CLOSING = 'closing';

    /**
     * @experimental
     */
    public const NETWORK_CONNECTION_STATE_VALUE_ESTABLISHED = 'established';

    /**
     * @experimental
     */
    public const NETWORK_CONNECTION_STATE_VALUE_FIN_WAIT_1 = 'fin_wait_1';

    /**
     * @experimental
     */
    public const NETWORK_CONNECTION_STATE_VALUE_FIN_WAIT_2 = 'fin_wait_2';

    /**
     * @experimental
     */
    public const NETWORK_CONNECTION_STATE_VALUE_LAST_ACK = 'last_ack';

    /**
     * @experimental
     */
    public const NETWORK_CONNECTION_STATE_VALUE_LISTEN = 'listen';

    /**
     * @experimental
     */
    public const NETWORK_CONNECTION_STATE_VALUE_SYN_RECEIVED = 'syn_received';

    /**
     * @experimental
     */
    public const NETWORK_CONNECTION_STATE_VALUE_SYN_SENT = 'syn_sent';

    /**
     * @experimental
     */
    public const NETWORK_CONNECTION_STATE_VALUE_TIME_WAIT = 'time_wait';

    /**
     * This describes more details regarding the connection.type. It may be the type of cell technology connection, but it could be used for describing details about a wifi connection.
     *
     * @experimental
     */
    public const NETWORK_CONNECTION_SUBTYPE = 'network.connection.subtype';

    /**
     * GPRS
     * @experimental
     */
    public const NETWORK_CONNECTION_SUBTYPE_VALUE_GPRS = 'gprs';

    /**
     * EDGE
     * @experimental
     */
    public const NETWORK_CONNECTION_SUBTYPE_VALUE_EDGE = 'edge';

    /**
     * UMTS
     * @experimental
     */
    public const NETWORK_CONNECTION_SUBTYPE_VALUE_UMTS = 'umts';

    /**
     * CDMA
     * @experimental
     */
    public const NETWORK_CONNECTION_SUBTYPE_VALUE_CDMA = 'cdma';

    /**
     * EVDO Rel. 0
     * @experimental
     */
    public const NETWORK_CONNECTION_SUBTYPE_VALUE_EVDO_0 = 'evdo_0';

    /**
     * EVDO Rev. A
     * @experimental
     */
    public const NETWORK_CONNECTION_SUBTYPE_VALUE_EVDO_A = 'evdo_a';

    /**
     * CDMA2000 1XRTT
     * @experimental
     */
    public const NETWORK_CONNECTION_SUBTYPE_VALUE_CDMA2000_1XRTT = 'cdma2000_1xrtt';

    /**
     * HSDPA
     * @experimental
     */
    public const NETWORK_CONNECTION_SUBTYPE_VALUE_HSDPA = 'hsdpa';

    /**
     * HSUPA
     * @experimental
     */
    public const NETWORK_CONNECTION_SUBTYPE_VALUE_HSUPA = 'hsupa';

    /**
     * HSPA
     * @experimental
     */
    public const NETWORK_CONNECTION_SUBTYPE_VALUE_HSPA = 'hspa';

    /**
     * IDEN
     * @experimental
     */
    public const NETWORK_CONNECTION_SUBTYPE_VALUE_IDEN = 'iden';

    /**
     * EVDO Rev. B
     * @experimental
     */
    public const NETWORK_CONNECTION_SUBTYPE_VALUE_EVDO_B = 'evdo_b';

    /**
     * LTE
     * @experimental
     */
    public const NETWORK_CONNECTION_SUBTYPE_VALUE_LTE = 'lte';

    /**
     * EHRPD
     * @experimental
     */
    public const NETWORK_CONNECTION_SUBTYPE_VALUE_EHRPD = 'ehrpd';

    /**
     * HSPAP
     * @experimental
     */
    public const NETWORK_CONNECTION_SUBTYPE_VALUE_HSPAP = 'hspap';

    /**
     * GSM
     * @experimental
     */
    public const NETWORK_CONNECTION_SUBTYPE_VALUE_GSM = 'gsm';

    /**
     * TD-SCDMA
     * @experimental
     */
    public const NETWORK_CONNECTION_SUBTYPE_VALUE_TD_SCDMA = 'td_scdma';

    /**
     * IWLAN
     * @experimental
     */
    public const NETWORK_CONNECTION_SUBTYPE_VALUE_IWLAN = 'iwlan';

    /**
     * 5G NR (New Radio)
     * @experimental
     */
    public const NETWORK_CONNECTION_SUBTYPE_VALUE_NR = 'nr';

    /**
     * 5G NRNSA (New Radio Non-Standalone)
     * @experimental
     */
    public const NETWORK_CONNECTION_SUBTYPE_VALUE_NRNSA = 'nrnsa';

    /**
     * LTE CA
     * @experimental
     */
    public const NETWORK_CONNECTION_SUBTYPE_VALUE_LTE_CA = 'lte_ca';

    /**
     * The internet connection type.
     *
     * @experimental
     */
    public const NETWORK_CONNECTION_TYPE = 'network.connection.type';

    /**
     * @experimental
     */
    public const NETWORK_CONNECTION_TYPE_VALUE_WIFI = 'wifi';

    /**
     * @experimental
     */
    public const NETWORK_CONNECTION_TYPE_VALUE_WIRED = 'wired';

    /**
     * @experimental
     */
    public const NETWORK_CONNECTION_TYPE_VALUE_CELL = 'cell';

    /**
     * @experimental
     */
    public const NETWORK_CONNECTION_TYPE_VALUE_UNAVAILABLE = 'unavailable';

    /**
     * @experimental
     */
    public const NETWORK_CONNECTION_TYPE_VALUE_UNKNOWN = 'unknown';

    /**
     * The network interface name.
     *
     * @experimental
     */
    public const NETWORK_INTERFACE_NAME = 'network.interface.name';

    /**
     * The network IO operation direction.
     *
     * @experimental
     */
    public const NETWORK_IO_DIRECTION = 'network.io.direction';

    /**
     * @experimental
     */
    public const NETWORK_IO_DIRECTION_VALUE_TRANSMIT = 'transmit';

    /**
     * @experimental
     */
    public const NETWORK_IO_DIRECTION_VALUE_RECEIVE = 'receive';

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
