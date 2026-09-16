<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export\Http;

/**
 * Defines the maximum number of bytes that will be read from an OTLP HTTP
 * response body.
 *
 * Limiting the response body size protects against excessive memory usage
 * caused by a misconfigured or malicious server.
 *
 * @see https://github.com/open-telemetry/opentelemetry-php/issues/1932
 */
final class ResponseBodySizeLimit
{
    /**
     * 4 MiB — mirrors the limit used by the OpenTelemetry Go SDK.
     * Any response larger than this is truncated before proto-unmarshalling.
     */
    public const MAX_BYTES = 4 * 1024 * 1024; // 4 MiB
}
