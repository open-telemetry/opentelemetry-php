<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for server.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/server/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface ServerIncubatingAttributes
{
    /**
     * Server domain name if available without reverse DNS lookup; otherwise, IP address or UNIX domain socket name.
     * When observed from the client side, and when communicating through an intermediary, `server.address` SHOULD represent the server address behind any intermediaries, for example proxies, if it's available.
     *
     * @stable
     */
    public const SERVER_ADDRESS = 'server.address';

    /**
     * The [Autonomous System Number (ASN)](https://www.iana.org/assignments/as-numbers/as-numbers.xhtml) associated with the server IP address.
     * This attribute SHOULD only be set when the server address is an IP address and the ASN is available from routing, enrichment, or lookup data. The value SHOULD be represented as an integer without the `AS` prefix.
     *
     * @experimental
     */
    public const SERVER_AS_NUMBER = 'server.as.number';

    /**
     * The name of the organization associated with the autonomous system of the server IP address.
     * This attribute SHOULD only be set when `server.as.number` is set and the organization name is available. The value SHOULD be the organization name as provided by the lookup or enrichment source, not the AS name or short code.
     *
     * @experimental
     */
    public const SERVER_AS_ORGANIZATION = 'server.as.organization';

    /**
     * Server Media Access Control (MAC) address.
     * This attribute MUST identify a MAC address associated with the logical server
     * endpoint, including when that endpoint is behind intermediaries. If the server's
     * MAC address is unknown, this attribute MUST NOT be set. The MAC address of a
     * proxy, gateway, or next-hop router MUST NOT be used as a substitute.
     *
     * MAC addresses MUST be represented in [IEEE RA hexadecimal form](https://standards.ieee.org/wp-content/uploads/import/documents/tutorials/eui.pdf):
     * as hyphen-separated octets in uppercase hexadecimal form from most to least significant.
     *
     * > [!WARNING]> This attribute may contain sensitive (PII) information.
     *
     * @experimental
     */
    public const SERVER_MAC_ADDRESS = 'server.mac.address';

    /**
     * Server port number.
     * When observed from the client side, and when communicating through an intermediary, `server.port` SHOULD represent the server port behind any intermediaries, for example proxies, if it's available.
     *
     * @stable
     */
    public const SERVER_PORT = 'server.port';

    /**
     * The IP prefix associated with the server IP address, in CIDR notation.
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
     * This attribute MUST NOT be set unless `server.address` is set to an IPv4 or
     * IPv6 address. The address MUST use the same IP version as this prefix and
     * be contained in it.
     *
     * @experimental
     */
    public const SERVER_PREFIX = 'server.prefix';

    /**
     * Logical name of the service acting as the server in a connection-based network interaction.
     *
     * SHOULD be equal to the actual [`service.name`](/docs/resource/README.md#service) resource attribute of the logical server, if any. This identifies the server service whether the telemetry is recorded by the client, the server, or another observer.
     *
     * @experimental
     */
    public const SERVER_SERVICE_NAME = 'server.service.name';

    /**
     * Logical namespace of the service acting as the server in a connection-based network interaction.
     *
     * SHOULD be equal to the actual [`service.namespace`](/docs/resource/README.md#service) resource attribute of the logical server, if any. This identifies the server service's namespace whether the telemetry is recorded by the client, the server, or another observer. Together with `server.service.name`, this identifies the server service. An empty namespace is equivalent to an unspecified namespace.
     *
     * @experimental
     */
    public const SERVER_SERVICE_NAMESPACE = 'server.service.namespace';

}
