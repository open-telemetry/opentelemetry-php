<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Attributes;

/**
 * Semantic attributes and corresponding values for url.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/url/
 */
interface UrlAttributes
{
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
     * The [URI path](https://www.rfc-editor.org/rfc/rfc3986#section-3.3) component
     *
     * Sensitive content provided in `url.path` SHOULD be scrubbed when instrumentations can identify it.
     *
     * @stable
     */
    public const URL_PATH = 'url.path';

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
     * The [URI scheme](https://www.rfc-editor.org/rfc/rfc3986#section-3.1) component identifying the used protocol.
     *
     * @stable
     */
    public const URL_SCHEME = 'url.scheme';

}
