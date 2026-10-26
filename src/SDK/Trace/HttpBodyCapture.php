<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Records the HTTP server request body as `http.request.body.content`.
 *
 * Opt-in (OTEL_PHP_INSTRUMENTATION_HTTP_SERVER_CAPTURE_REQUEST_BODY), the recorded part is limited to
 * OTEL_PHP_INSTRUMENTATION_HTTP_SERVER_CAPTURE_REQUEST_BODY_MAX_SIZE bytes and cut on a UTF-8 character boundary.
 *
 * Only textual bodies are recorded, as a string. The PHP attribute model has no byte array type, so binary
 * bodies, bodies with a content coding (e.g. gzip) and bodies in a charset other than UTF-8 are not recorded.
 * The body stream position is restored, so the application is not affected.
 *
 * The body is recorded as is, without redaction, and may contain sensitive data such as passwords or tokens.
 * Enable it only where that is acceptable.
 *
 * @see https://github.com/open-telemetry/semantic-conventions/pull/3521
 * @experimental
 */
final class HttpBodyCapture
{
    /**
     * Not taken from the semantic conventions package on purpose: the attribute is not in any released
     * semantic conventions version yet (the latest, 1.44.0, lacks it), and this package still allows older
     * open-telemetry/sem-conv versions.
     *
     * @see https://github.com/open-telemetry/semantic-conventions/pull/3521 Merged, but not in a released version yet (expected in 1.45); pin it to that tag once it is.
     */
    private const REQUEST_BODY_CONTENT = 'http.request.body.content';

    private const READ_CHUNK_SIZE = 8192;

    private const TEXTUAL_SUBTYPE_SUFFIXES = ['/json', '+json', '/xml', '+xml', '/yaml', '+yaml'];

    public static function isEnabled(): bool
    {
        return Configuration::getBoolean(Variables::OTEL_PHP_INSTRUMENTATION_HTTP_SERVER_CAPTURE_REQUEST_BODY);
    }

    /**
     * Sets `http.request.body.content` on the span when capturing is enabled and the body is recordable.
     */
    public static function captureRequestBody(SpanInterface $span, ServerRequestInterface $request): void
    {
        if (!$span->isRecording() || !self::isEnabled()) {
            return;
        }
        $body = self::extract(
            $request,
            Configuration::getInt(Variables::OTEL_PHP_INSTRUMENTATION_HTTP_SERVER_CAPTURE_REQUEST_BODY_MAX_SIZE),
        );
        if ($body !== null) {
            $span->setAttribute(self::REQUEST_BODY_CONTENT, $body);
        }
    }

    /**
     * @param int $maxSize maximum number of bytes to record
     * @return string|null the (possibly truncated) body, or null when there is nothing recordable
     */
    public static function extract(ServerRequestInterface $request, int $maxSize): ?string
    {
        if ($maxSize <= 0) {
            return null;
        }
        $contentType = $request->getHeaderLine('Content-Type');
        if (!self::isTextual($contentType)
            || self::hasContentCoding($request->getHeaderLine('Content-Encoding'))
            || !self::isUtf8Charset($contentType)
        ) {
            return null;
        }

        // One byte more than the limit tells whether the body was actually cut
        $raw = self::read($request, $maxSize + 1);
        if ($raw === null || $raw === '') {
            return null;
        }
        $cut = strlen($raw) > $maxSize;

        return self::toValidUtf8($cut ? substr($raw, 0, $maxSize) : $raw, $cut);
    }

    /**
     * Content-Type heuristic from the semantic conventions: type/subtype (case-insensitive, without parameters)
     * starts with `text/`, ends with `/json`, `+json`, `/xml`, `+xml`, `/yaml` or `+yaml`, is
     * `application/x-www-form-urlencoded`, or the header declares a `charset` parameter.
     */
    public static function isTextual(string $contentType): bool
    {
        $parts = explode(';', $contentType);
        $mediaType = strtolower(trim($parts[0]));
        if ($mediaType === '') {
            return false;
        }
        if (str_starts_with($mediaType, 'text/') || $mediaType === 'application/x-www-form-urlencoded') {
            return true;
        }
        foreach (self::TEXTUAL_SUBTYPE_SUFFIXES as $suffix) {
            if (str_ends_with($mediaType, $suffix)) {
                return true;
            }
        }

        return self::charset($contentType) !== null;
    }

    private static function charset(string $contentType): ?string
    {
        $parts = explode(';', $contentType);
        array_shift($parts);
        foreach ($parts as $part) {
            $pair = explode('=', $part, 2);
            if (count($pair) === 2 && strtolower(trim($pair[0])) === 'charset') {
                return strtolower(trim($pair[1], " \t\"'"));
            }
        }

        return null;
    }

    private static function isUtf8Charset(string $contentType): bool
    {
        $charset = self::charset($contentType);

        return $charset === null || $charset === 'utf-8' || $charset === 'utf8' || $charset === 'us-ascii';
    }

    private static function hasContentCoding(string $contentEncoding): bool
    {
        $encoding = strtolower(trim($contentEncoding));

        return $encoding !== '' && $encoding !== 'identity';
    }

    private static function read(ServerRequestInterface $request, int $maxSize): ?string
    {
        $stream = $request->getBody();
        if (!$stream->isReadable() || !$stream->isSeekable()) {
            return null;
        }

        $position = null;

        try {
            $position = $stream->tell();
            $stream->rewind();
            // A single read() may return less than requested (php://input returns at most 8192 bytes at a time),
            // and a large request would allocate that much memory up front, so read in bounded chunks
            $data = '';
            while (strlen($data) < $maxSize && !$stream->eof()) {
                $chunk = $stream->read(min($maxSize - strlen($data), self::READ_CHUNK_SIZE));
                if ($chunk === '') {
                    break;
                }
                $data .= $chunk;
            }
        } catch (\RuntimeException) {
            return null;
        } finally {
            if ($position !== null) {
                try {
                    $stream->seek($position);
                } catch (\RuntimeException) {
                    // nothing more can be done
                }
            }
        }

        return $data;
    }

    /**
     * When the body was cut at the size limit, drops the incomplete trailing multi-byte character.
     * Returns null when the data is not valid UTF-8 (a body that was not cut is never altered).
     */
    private static function toValidUtf8(string $data, bool $cut): ?string
    {
        for ($i = 0; $cut && $i < 3 && $data !== '' && preg_match('//u', $data) !== 1; $i++) {
            $data = substr($data, 0, -1);
        }

        return $data !== '' && preg_match('//u', $data) === 1 ? $data : null;
    }
}
