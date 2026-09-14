<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export\Http;

use function function_exists;
use function gzdecode;
use Psr\Http\Message\ResponseInterface;
use function strlen;
use function substr;

/**
 * Utility helpers for PSR-7 OTLP/HTTP responses.
 *
 * Key change (issue #1932): response bodies are now read through
 * {@see PsrUtils::readBodyWithSizeLimit()}, which caps consumption at
 * {@see ResponseBodySizeLimit::MAX_BYTES} (4 MiB) to protect against
 * memory exhaustion from misconfigured or malicious collectors.
 */
final class PsrUtils
{
    /**
     * Read the response body, honouring the Content-Length header when
     * available and never consuming more than
     * {@see ResponseBodySizeLimit::MAX_BYTES} bytes.
     *
     * Behaviour mirrors the Go SDK implementation added in
     * https://github.com/open-telemetry/opentelemetry-go/pull/XXXX:
     *
     *  1. If Content-Length is 0  → return empty string immediately.
     *  2. If Content-Length > 0   → read exactly that many bytes, but cap at
     *                               MAX_BYTES.
     *  3. If Content-Length is -1 → header absent; read up to MAX_BYTES.
     *
     * @param ResponseInterface $response PSR-7 response whose body to read.
     *
     * @return string Raw (possibly compressed) bytes.
     */
    public static function readBodyWithSizeLimit(ResponseInterface $response): string
    {
        $contentLength = $response->getBody()->getSize();

        // (1) Server explicitly said there is no body.
        if ($contentLength === 0) {
            return '';
        }

        $maxRead = ResponseBodySizeLimit::MAX_BYTES;

        // (2) Server provided a Content-Length we can trust.
        if ($contentLength !== null && $contentLength > 0) {
            // Still cap at MAX_BYTES; a Content-Length larger than our limit
            // is treated as if no Content-Length were supplied — we read up
            // to MAX_BYTES and let proto-unmarshalling fail on truncation.
            $maxRead = min($contentLength, ResponseBodySizeLimit::MAX_BYTES);
        }

        // (3) Read at most $maxRead bytes.
        $body = $response->getBody()->read($maxRead);

        return $body;
    }

    /**
     * Decode a response body, respecting Content-Encoding, and enforcing the
     * 4 MiB body-size cap before decompression.
     *
     * @param ResponseInterface $response
     *
     * @return string Decoded payload bytes, ready for proto-unmarshalling.
     */
    public static function decode(ResponseInterface $response): string
    {
        $body = self::readBodyWithSizeLimit($response);

        if ($body === '') {
            return '';
        }

        $encoding = strtolower($response->getHeaderLine('Content-Encoding'));

        if ($encoding === 'gzip') {
            if (!function_exists('gzdecode')) {
                throw new \RuntimeException(
                    'gzip Content-Encoding received but the gzdecode() function is unavailable. '
                    . 'Ensure the zlib PHP extension is installed.'
                );
            }

            $decoded = @gzdecode($body);

            if ($decoded === false) {
                throw new \RuntimeException(
                    'Failed to gzip-decode OTLP response body.'
                );
            }

            return $decoded;
        }

        return $body;
    }
}
