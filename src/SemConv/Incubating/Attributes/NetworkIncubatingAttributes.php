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
     * This describes more details regarding the connection.type. It may be the type of cell technology connection, but it could be used for describing details about a Wi-Fi connection.
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
     * Wi-Fi
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
     * The direction of traffic from the perspective of the observing host's physical or virtual network interface. It should not be used to represent the logical direction of a stateful connection or network flow.
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
     * Local address of the network connection - IP address or UNIX domain socket name.
     *
     * @stable
     */
    public const NETWORK_LOCAL_ADDRESS = 'network.local.address';

    /**
     * The [Autonomous System Number (ASN)](https://www.iana.org/assignments/as-numbers/as-numbers.xhtml) associated with the local IP address.
     * This attribute SHOULD only be set when the local address is an IP address and the ASN is available from routing, enrichment, or lookup data. The value SHOULD be represented as an integer without the `AS` prefix.
     *
     * @experimental
     */
    public const NETWORK_LOCAL_AS_NUMBER = 'network.local.as.number';

    /**
     * The name of the organization associated with the autonomous system of the local IP address.
     * This attribute SHOULD only be set when `network.local.as.number` is set and the organization name is available. The value SHOULD be the organization name as provided by the lookup or enrichment source, not the AS name or short code.
     *
     * @experimental
     */
    public const NETWORK_LOCAL_AS_ORGANIZATION = 'network.local.as.organization';

    /**
     * Local Media Access Control (MAC) address of the network connection.
     * MAC addresses MUST be represented in [IEEE RA hexadecimal form](https://standards.ieee.org/wp-content/uploads/import/documents/tutorials/eui.pdf):
     * as hyphen-separated octets in uppercase hexadecimal form from most to least significant.
     *
     * > [!WARNING]> This attribute may contain sensitive (PII) information.
     *
     * @experimental
     */
    public const NETWORK_LOCAL_MAC_ADDRESS = 'network.local.mac.address';

    /**
     * Local port number of the network connection.
     *
     * @stable
     */
    public const NETWORK_LOCAL_PORT = 'network.local.port';

    /**
     * The IP prefix associated with the local IP address, in CIDR notation.
     *
     * > [!WARNING]> This attribute may contain sensitive (PII) information. A `/32`>  IPv4 prefix
     * > or `/128`>  IPv6 prefix preserves the complete endpoint IP address.
     * > Using CIDR notation does not guarantee that the data is anonymous.
     *
     * The value MUST be an IPv4 or IPv6 network prefix in CIDR notation, as defined in
     * [RFC 4632](https://www.rfc-editor.org/rfc/rfc4632.html) for IPv4 and
     * [RFC 4291](https://www.rfc-editor.org/rfc/rfc4291.html#section-2.3) for IPv6.
     * IPv4 prefixes MUST use a dotted-decimal address followed by `/` and a decimal
     * prefix length from 0 to 32. IPv6 prefixes MUST use the canonical address text
     * representation defined in [RFC 5952](https://www.rfc-editor.org/rfc/rfc5952.html),
     * followed by `/` and a decimal prefix length from 0 to 128. All host bits in
     * the address MUST be set to zero. The value MUST NOT contain a domain name,
     * UNIX domain socket name, or IPv6 zone identifier.
     *
     * Instrumentation or Collector receivers that populate this attribute MUST
     * convert native representations, such as an address and subnet mask or an
     * address and prefix length, to equivalent CIDR notation before emitting it.
     *
     * This attribute MAY be set whether or not `network.local.address` is set. If
     * `network.local.address` is also set to an IP address, it MUST use the same IP version
     * and be contained in this prefix.
     *
     * @experimental
     */
    public const NETWORK_LOCAL_PREFIX = 'network.local.prefix';

    /**
     * Peer address of the network connection - IP address or UNIX domain socket name.
     *
     * @stable
     */
    public const NETWORK_PEER_ADDRESS = 'network.peer.address';

    /**
     * The [Autonomous System Number (ASN)](https://www.iana.org/assignments/as-numbers/as-numbers.xhtml) associated with the peer IP address.
     * This attribute SHOULD only be set when the peer address is an IP address and the ASN is available from routing, enrichment, or lookup data. The value SHOULD be represented as an integer without the `AS` prefix.
     *
     * @experimental
     */
    public const NETWORK_PEER_AS_NUMBER = 'network.peer.as.number';

    /**
     * The name of the organization associated with the autonomous system of the peer IP address.
     * This attribute SHOULD only be set when `network.peer.as.number` is set and the organization name is available. The value SHOULD be the organization name as provided by the lookup or enrichment source, not the AS name or short code.
     *
     * @experimental
     */
    public const NETWORK_PEER_AS_ORGANIZATION = 'network.peer.as.organization';

    /**
     * Peer Media Access Control (MAC) address of the network connection.
     * MAC addresses MUST be represented in [IEEE RA hexadecimal form](https://standards.ieee.org/wp-content/uploads/import/documents/tutorials/eui.pdf):
     * as hyphen-separated octets in uppercase hexadecimal form from most to least significant.
     *
     * > [!WARNING]> This attribute may contain sensitive (PII) information.
     *
     * @experimental
     */
    public const NETWORK_PEER_MAC_ADDRESS = 'network.peer.mac.address';

    /**
     * Peer port number of the network connection.
     *
     * @stable
     */
    public const NETWORK_PEER_PORT = 'network.peer.port';

    /**
     * The IP prefix associated with the peer IP address, in CIDR notation.
     *
     * > [!WARNING]> This attribute may contain sensitive (PII) information. A `/32`>  IPv4 prefix
     * > or `/128`>  IPv6 prefix preserves the complete endpoint IP address.
     * > Using CIDR notation does not guarantee that the data is anonymous.
     *
     * The value MUST be an IPv4 or IPv6 network prefix in CIDR notation, as defined in
     * [RFC 4632](https://www.rfc-editor.org/rfc/rfc4632.html) for IPv4 and
     * [RFC 4291](https://www.rfc-editor.org/rfc/rfc4291.html#section-2.3) for IPv6.
     * IPv4 prefixes MUST use a dotted-decimal address followed by `/` and a decimal
     * prefix length from 0 to 32. IPv6 prefixes MUST use the canonical address text
     * representation defined in [RFC 5952](https://www.rfc-editor.org/rfc/rfc5952.html),
     * followed by `/` and a decimal prefix length from 0 to 128. All host bits in
     * the address MUST be set to zero. The value MUST NOT contain a domain name,
     * UNIX domain socket name, or IPv6 zone identifier.
     *
     * Instrumentation or Collector receivers that populate this attribute MUST
     * convert native representations, such as an address and subnet mask or an
     * address and prefix length, to equivalent CIDR notation before emitting it.
     *
     * This attribute MAY be set whether or not `network.peer.address` is set. If
     * `network.peer.address` is also set to an IP address, it MUST use the same IP version
     * and be contained in this prefix.
     *
     * @experimental
     */
    public const NETWORK_PEER_PREFIX = 'network.peer.prefix';

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
     * UNIX domain socket
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
