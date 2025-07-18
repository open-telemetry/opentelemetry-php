<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for url.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/url/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface UrlIncubatingAttributes
{
    /**
     * Domain extracted from the `url.full`, such as "opentelemetry.io".
     *
     * In some cases a URL may refer to an IP and/or port directly, without a domain name. In this case, the IP address would go to the domain field. If the URL contains a [literal IPv6 address](https://www.rfc-editor.org/rfc/rfc2732#section-2) enclosed by `[` and `]`, the `[` and `]` characters should also be captured in the domain field.
     *
     * @experimental
     */
    public const URL_DOMAIN = 'url.domain';

    /**
     * The file extension extracted from the `url.full`, excluding the leading dot.
     *
     * The file extension is only set if it exists, as not every url has a file extension. When the file name has multiple extensions `example.tar.gz`, only the last one should be captured `gz`, not `tar.gz`.
     *
     * @experimental
     */
    public const URL_EXTENSION = 'url.extension';

    /**
     * The [URI fragment](https://www.rfc-editor.org/rfc/rfc3986#section-3.5) component
     *
     * @stable
     */
    public const URL_FRAGMENT = 'url.fragment';

    /**
     * Absolute URL describing a network resource according to [RFC3986](https://www.rfc-editor.org/rfc/rfc3986)
     * For network calls, URL usually has `scheme://host[:port][path][?query][#fragment]` format, where the fragment
     * is not transmitted over HTTP, but if it is known, it SHOULD be included nevertheless.
     *
     * `url.full` MUST NOT contain credentials passed via URL in form of `https://username:password@www.example.com/`.
     * In such case username and password SHOULD be redacted and attribute's value SHOULD be `https://REDACTED:REDACTED@www.example.com/`.
     *
     * `url.full` SHOULD capture the absolute URL when it is available (or can be reconstructed).
     *
     * Sensitive content provided in `url.full` SHOULD be scrubbed when instrumentations can identify it.
     *
     *
     * Query string values for the following keys SHOULD be redacted by default and replaced by the
     * value `REDACTED`:
     *
     * - [`AWSAccessKeyId`](https://docs.aws.amazon.com/AmazonS3/latest/userguide/RESTAuthentication.html#RESTAuthenticationQueryStringAuth)
     * - [`Signature`](https://docs.aws.amazon.com/AmazonS3/latest/userguide/RESTAuthentication.html#RESTAuthenticationQueryStringAuth)
     * - [`sig`](https://learn.microsoft.com/azure/storage/common/storage-sas-overview#sas-token)
     * - [`X-Goog-Signature`](https://cloud.google.com/storage/docs/access-control/signed-urls)
     *
     * This list is subject to change over time.
     *
     * When a query string value is redacted, the query string key SHOULD still be preserved, e.g.
     * `https://www.example.com/path?color=blue&sig=REDACTED`.
     *
     * @stable
     */
    public const URL_FULL = 'url.full';

    /**
     * Unmodified original URL as seen in the event source.
     *
     * In network monitoring, the observed URL may be a full URL, whereas in access logs, the URL is often just represented as a path. This field is meant to represent the URL as it was observed, complete or not.
     * `url.original` might contain credentials passed via URL in form of `https://username:password@www.example.com/`. In such case password and username SHOULD NOT be redacted and attribute's value SHOULD remain the same.
     *
     * @experimental
     */
    public const URL_ORIGINAL = 'url.original';

    /**
     * The [URI path](https://www.rfc-editor.org/rfc/rfc3986#section-3.3) component
     *
     * Sensitive content provided in `url.path` SHOULD be scrubbed when instrumentations can identify it.
     *
     * @stable
     */
    public const URL_PATH = 'url.path';

    /**
     * Port extracted from the `url.full`
     *
     * @experimental
     */
    public const URL_PORT = 'url.port';

    /**
     * The [URI query](https://www.rfc-editor.org/rfc/rfc3986#section-3.4) component
     *
     * Sensitive content provided in `url.query` SHOULD be scrubbed when instrumentations can identify it.
     *
     *
     * Query string values for the following keys SHOULD be redacted by default and replaced by the value `REDACTED`:
     *
     * - [`AWSAccessKeyId`](https://docs.aws.amazon.com/AmazonS3/latest/userguide/RESTAuthentication.html#RESTAuthenticationQueryStringAuth)
     * - [`Signature`](https://docs.aws.amazon.com/AmazonS3/latest/userguide/RESTAuthentication.html#RESTAuthenticationQueryStringAuth)
     * - [`sig`](https://learn.microsoft.com/azure/storage/common/storage-sas-overview#sas-token)
     * - [`X-Goog-Signature`](https://cloud.google.com/storage/docs/access-control/signed-urls)
     *
     * This list is subject to change over time.
     *
     * When a query string value is redacted, the query string key SHOULD still be preserved, e.g.
     * `q=OpenTelemetry&sig=REDACTED`.
     *
     * @stable
     */
    public const URL_QUERY = 'url.query';

    /**
     * The highest registered url domain, stripped of the subdomain.
     *
     * This value can be determined precisely with the [public suffix list](https://publicsuffix.org/). For example, the registered domain for `foo.example.com` is `example.com`. Trying to approximate this by simply taking the last two labels will not work well for TLDs such as `co.uk`.
     *
     * @experimental
     */
    public const URL_REGISTERED_DOMAIN = 'url.registered_domain';

    /**
     * The [URI scheme](https://www.rfc-editor.org/rfc/rfc3986#section-3.1) component identifying the used protocol.
     *
     * @stable
     */
    public const URL_SCHEME = 'url.scheme';

    /**
     * The subdomain portion of a fully qualified domain name includes all of the names except the host name under the registered_domain. In a partially qualified domain, or if the qualification level of the full name cannot be determined, subdomain contains all of the names below the registered domain.
     *
     * The subdomain portion of `www.east.mydomain.co.uk` is `east`. If the domain has multiple levels of subdomain, such as `sub2.sub1.example.com`, the subdomain field should contain `sub2.sub1`, with no trailing period.
     *
     * @experimental
     */
    public const URL_SUBDOMAIN = 'url.subdomain';

    /**
     * The low-cardinality template of an [absolute path reference](https://www.rfc-editor.org/rfc/rfc3986#section-4.2).
     *
     * @experimental
     */
    public const URL_TEMPLATE = 'url.template';

    /**
     * The effective top level domain (eTLD), also known as the domain suffix, is the last part of the domain name. For example, the top level domain for example.com is `com`.
     *
     * This value can be determined precisely with the [public suffix list](https://publicsuffix.org/).
     *
     * @experimental
     */
    public const URL_TOP_LEVEL_DOMAIN = 'url.top_level_domain';

}
