<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for tls.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/tls/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface TlsIncubatingAttributes
{
    /**
     * String indicating the [cipher](https://datatracker.ietf.org/doc/html/rfc5246#appendix-A.5) used during the current connection.
     *
     * The values allowed for `tls.cipher` MUST be one of the `Descriptions` of the [registered TLS Cipher Suits](https://www.iana.org/assignments/tls-parameters/tls-parameters.xhtml#table-tls-parameters-4).
     *
     * @experimental
     */
    public const TLS_CIPHER = 'tls.cipher';

    /**
     * PEM-encoded stand-alone certificate offered by the client. This is usually mutually-exclusive of `client.certificate_chain` since this value also exists in that list.
     *
     * @experimental
     */
    public const TLS_CLIENT_CERTIFICATE = 'tls.client.certificate';

    /**
     * Array of PEM-encoded certificates that make up the certificate chain offered by the client. This is usually mutually-exclusive of `client.certificate` since that value should be the first certificate in the chain.
     *
     * @experimental
     */
    public const TLS_CLIENT_CERTIFICATE_CHAIN = 'tls.client.certificate_chain';

    /**
     * Certificate fingerprint using the MD5 digest of DER-encoded version of certificate offered by the client. For consistency with other hash values, this value should be formatted as an uppercase hash.
     *
     * @experimental
     */
    public const TLS_CLIENT_HASH_MD5 = 'tls.client.hash.md5';

    /**
     * Certificate fingerprint using the SHA1 digest of DER-encoded version of certificate offered by the client. For consistency with other hash values, this value should be formatted as an uppercase hash.
     *
     * @experimental
     */
    public const TLS_CLIENT_HASH_SHA1 = 'tls.client.hash.sha1';

    /**
     * Certificate fingerprint using the SHA256 digest of DER-encoded version of certificate offered by the client. For consistency with other hash values, this value should be formatted as an uppercase hash.
     *
     * @experimental
     */
    public const TLS_CLIENT_HASH_SHA256 = 'tls.client.hash.sha256';

    /**
     * Distinguished name of [subject](https://datatracker.ietf.org/doc/html/rfc5280#section-4.1.2.6) of the issuer of the x.509 certificate presented by the client.
     *
     * @experimental
     */
    public const TLS_CLIENT_ISSUER = 'tls.client.issuer';

    /**
     * A hash that identifies clients based on how they perform an SSL/TLS handshake.
     *
     * @experimental
     */
    public const TLS_CLIENT_JA3 = 'tls.client.ja3';

    /**
     * Date/Time indicating when client certificate is no longer considered valid.
     *
     * @experimental
     */
    public const TLS_CLIENT_NOT_AFTER = 'tls.client.not_after';

    /**
     * Date/Time indicating when client certificate is first considered valid.
     *
     * @experimental
     */
    public const TLS_CLIENT_NOT_BEFORE = 'tls.client.not_before';

    /**
     * Distinguished name of subject of the x.509 certificate presented by the client.
     *
     * @experimental
     */
    public const TLS_CLIENT_SUBJECT = 'tls.client.subject';

    /**
     * Array of ciphers offered by the client during the client hello.
     *
     * @experimental
     */
    public const TLS_CLIENT_SUPPORTED_CIPHERS = 'tls.client.supported_ciphers';

    /**
     * String indicating the curve used for the given cipher, when applicable
     *
     * @experimental
     */
    public const TLS_CURVE = 'tls.curve';

    /**
     * Boolean flag indicating if the TLS negotiation was successful and transitioned to an encrypted tunnel.
     *
     * @experimental
     */
    public const TLS_ESTABLISHED = 'tls.established';

    /**
     * String indicating the protocol being tunneled. Per the values in the [IANA registry](https://www.iana.org/assignments/tls-extensiontype-values/tls-extensiontype-values.xhtml#alpn-protocol-ids), this string should be lower case.
     *
     * @experimental
     */
    public const TLS_NEXT_PROTOCOL = 'tls.next_protocol';

    /**
     * Normalized lowercase protocol name parsed from original string of the negotiated [SSL/TLS protocol version](https://docs.openssl.org/1.1.1/man3/SSL_get_version/#return-values)
     *
     * @experimental
     */
    public const TLS_PROTOCOL_NAME = 'tls.protocol.name';

    /**
     * @experimental
     */
    public const TLS_PROTOCOL_NAME_VALUE_SSL = 'ssl';

    /**
     * @experimental
     */
    public const TLS_PROTOCOL_NAME_VALUE_TLS = 'tls';

    /**
     * Numeric part of the version parsed from the original string of the negotiated [SSL/TLS protocol version](https://docs.openssl.org/1.1.1/man3/SSL_get_version/#return-values)
     *
     * @experimental
     */
    public const TLS_PROTOCOL_VERSION = 'tls.protocol.version';

    /**
     * Boolean flag indicating if this TLS connection was resumed from an existing TLS negotiation.
     *
     * @experimental
     */
    public const TLS_RESUMED = 'tls.resumed';

    /**
     * PEM-encoded stand-alone certificate offered by the server. This is usually mutually-exclusive of `server.certificate_chain` since this value also exists in that list.
     *
     * @experimental
     */
    public const TLS_SERVER_CERTIFICATE = 'tls.server.certificate';

    /**
     * Array of PEM-encoded certificates that make up the certificate chain offered by the server. This is usually mutually-exclusive of `server.certificate` since that value should be the first certificate in the chain.
     *
     * @experimental
     */
    public const TLS_SERVER_CERTIFICATE_CHAIN = 'tls.server.certificate_chain';

    /**
     * Certificate fingerprint using the MD5 digest of DER-encoded version of certificate offered by the server. For consistency with other hash values, this value should be formatted as an uppercase hash.
     *
     * @experimental
     */
    public const TLS_SERVER_HASH_MD5 = 'tls.server.hash.md5';

    /**
     * Certificate fingerprint using the SHA1 digest of DER-encoded version of certificate offered by the server. For consistency with other hash values, this value should be formatted as an uppercase hash.
     *
     * @experimental
     */
    public const TLS_SERVER_HASH_SHA1 = 'tls.server.hash.sha1';

    /**
     * Certificate fingerprint using the SHA256 digest of DER-encoded version of certificate offered by the server. For consistency with other hash values, this value should be formatted as an uppercase hash.
     *
     * @experimental
     */
    public const TLS_SERVER_HASH_SHA256 = 'tls.server.hash.sha256';

    /**
     * Distinguished name of [subject](https://datatracker.ietf.org/doc/html/rfc5280#section-4.1.2.6) of the issuer of the x.509 certificate presented by the client.
     *
     * @experimental
     */
    public const TLS_SERVER_ISSUER = 'tls.server.issuer';

    /**
     * A hash that identifies servers based on how they perform an SSL/TLS handshake.
     *
     * @experimental
     */
    public const TLS_SERVER_JA3S = 'tls.server.ja3s';

    /**
     * Date/Time indicating when server certificate is no longer considered valid.
     *
     * @experimental
     */
    public const TLS_SERVER_NOT_AFTER = 'tls.server.not_after';

    /**
     * Date/Time indicating when server certificate is first considered valid.
     *
     * @experimental
     */
    public const TLS_SERVER_NOT_BEFORE = 'tls.server.not_before';

    /**
     * Distinguished name of subject of the x.509 certificate presented by the server.
     *
     * @experimental
     */
    public const TLS_SERVER_SUBJECT = 'tls.server.subject';

}
