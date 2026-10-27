<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API\Behavior\LogsMessagesTrait;
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
    use LogsMessagesTrait;

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
        [$mediaType, $parameters] = self::parseContentType($contentType);
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

        return isset($parameters['charset']);
    }

    /**
     * Splits a Content-Type header value into the lower case media type and its parameters (lower case names).
     * Semicolons and escaped characters inside quoted values do not separate parameters.
     *
     * @return array{0: string, 1: array<string, string>}
     */
    private static function parseContentType(string $contentType): array
    {
        $parts = [];
        $current = '';
        $quoted = false;
        $length = strlen($contentType);
        for ($i = 0; $i < $length; $i++) {
            $char = $contentType[$i];
            if ($quoted && $char === '\\' && $i + 1 < $length) {
                $current .= $char . $contentType[++$i];

                continue;
            }
            if ($char === '"') {
                $quoted = !$quoted;
            }
            if ($char === ';' && !$quoted) {
                $parts[] = $current;
                $current = '';

                continue;
            }
            $current .= $char;
        }
        $parts[] = $current;

        $mediaType = strtolower(trim(array_shift($parts)));
        $parameters = [];
        foreach ($parts as $part) {
            $pair = explode('=', $part, 2);
            if (count($pair) !== 2) {
                continue;
            }
            $name = strtolower(trim($pair[0]));
            $value = trim($pair[1]);
            if (preg_match('/^"((?:[^"\\\\]|\\\\.)*)"?$/s', $value, $m) === 1) {
                $value = preg_replace('/\\\\(.)/s', '$1', $m[1]) ?? '';
            }
            $parameters[$name] ??= strtolower(trim($value, " \t'"));
        }

        return [$mediaType, $parameters];
    }

    /**
     * Logging is best effort and must never break the request.
     */
    private static function warn(string $message, array $context): void
    {
        try {
            self::logWarning($message, $context);
        } catch (\Throwable) {
            // nothing more can be done
        }
    }

    private static function isUtf8Charset(string $contentType): bool
    {
        $charset = self::parseContentType($contentType)[1]['charset'] ?? null;

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
                } catch (\RuntimeException $e) {
                    // the body is still returned, but the application may see another stream position
                    self::warn('Unable to restore the request body stream position', ['exception' => $e]);
                }
            }
        }

        return $data;
    }

    /**
     * When the body was cut at the size limit, drops the trailing bytes of a valid but incomplete multi-byte
     * character. Returns null when the data is not valid UTF-8 (a body that was not cut is never altered, and
     * malformed bytes are never dropped).
     */
    private static function toValidUtf8(string $data, bool $cut): ?string
    {
        if ($cut) {
            $data = substr($data, 0, strlen($data) - self::incompleteTrailingBytes($data));
        }

        return $data !== '' && preg_match('//u', $data) === 1 ? $data : null;
    }

    /**
     * Number of trailing bytes that start a multi-byte character without finishing it (0 to 3).
     */
    private static function incompleteTrailingBytes(string $data): int
    {
        $length = strlen($data);
        for ($back = 1; $back <= 3 && $back <= $length; $back++) {
            $byte = ord($data[$length - $back]);
            if (($byte & 0xC0) === 0x80) {
                continue; // continuation byte, keep looking for its lead byte
            }
            $expected = match (true) {
                $byte >= 0xF0 && $byte <= 0xF4 => 4,
                $byte >= 0xE0 && $byte <= 0xEF => 3,
                $byte >= 0xC2 && $byte <= 0xDF => 2,
                default => 1,
            };

            return $expected > $back ? $back : 0;
        }

        return 0;
    }
}
