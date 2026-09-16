<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\SDK\Common\Export\Http;

use OpenTelemetry\SDK\Common\Export\Http\PsrUtils;
use OpenTelemetry\SDK\Common\Export\Http\ResponseBodySizeLimit;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Tests for response body size limitation (issue #1932).
 *
 * @covers \OpenTelemetry\SDK\Common\Export\Http\PsrUtils
 * @covers \OpenTelemetry\SDK\Common\Export\Http\ResponseBodySizeLimit
 */
final class ResponseBodySizeLimitTest extends TestCase
{
    // -------------------------------------------------------------------------
    // ResponseBodySizeLimit constant
    // -------------------------------------------------------------------------

    public function test_max_bytes_constant_is_four_mib(): void
    {
        $this->assertSame(4 * 1024 * 1024, ResponseBodySizeLimit::MAX_BYTES);
    }

    // -------------------------------------------------------------------------
    // PsrUtils::readBodyWithSizeLimit
    // -------------------------------------------------------------------------

    public function test_read_body_returns_empty_string_when_content_length_is_zero(): void
    {
        $response = $this->makeResponse(bodyContent: '', contentLength: 0, encoding: null);

        $result = PsrUtils::readBodyWithSizeLimit($response);

        $this->assertSame('', $result);
    }

    public function test_read_body_reads_exact_bytes_when_content_length_smaller_than_limit(): void
    {
        $payload = str_repeat('x', 100);
        $response = $this->makeResponse(bodyContent: $payload, contentLength: 100, encoding: null);

        $result = PsrUtils::readBodyWithSizeLimit($response);

        $this->assertSame($payload, $result);
    }

    public function test_read_body_caps_at_max_bytes_when_content_length_is_null(): void
    {
        // Simulate missing Content-Length header (getSize() returns null).
        $oversized = str_repeat('y', ResponseBodySizeLimit::MAX_BYTES + 1);

        // The stream will only return MAX_BYTES when read() is called with that arg.
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getSize')->willReturn(null);
        $stream->method('read')
               ->with(ResponseBodySizeLimit::MAX_BYTES)
               ->willReturn(substr($oversized, 0, ResponseBodySizeLimit::MAX_BYTES));

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $result = PsrUtils::readBodyWithSizeLimit($response);

        $this->assertSame(ResponseBodySizeLimit::MAX_BYTES, strlen($result));
    }

    public function test_read_body_caps_at_max_bytes_when_content_length_exceeds_limit(): void
    {
        $tooLarge = ResponseBodySizeLimit::MAX_BYTES + 512;

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getSize')->willReturn($tooLarge);
        // read() must be called with MAX_BYTES, not $tooLarge.
        $stream->method('read')
               ->with(ResponseBodySizeLimit::MAX_BYTES)
               ->willReturn(str_repeat('z', ResponseBodySizeLimit::MAX_BYTES));

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $result = PsrUtils::readBodyWithSizeLimit($response);

        $this->assertSame(ResponseBodySizeLimit::MAX_BYTES, strlen($result));
    }

    // -------------------------------------------------------------------------
    // PsrUtils::decode – plain (no compression)
    // -------------------------------------------------------------------------

    public function test_decode_returns_plain_body_when_no_content_encoding(): void
    {
        $payload = 'protobuf-bytes';
        $response = $this->makeResponse(
            bodyContent: $payload,
            contentLength: strlen($payload),
            encoding: null,
        );

        $this->assertSame($payload, PsrUtils::decode($response));
    }

    public function test_decode_returns_empty_string_for_empty_body(): void
    {
        $response = $this->makeResponse(bodyContent: '', contentLength: 0, encoding: null);

        $this->assertSame('', PsrUtils::decode($response));
    }

    // -------------------------------------------------------------------------
    // PsrUtils::decode – gzip
    // -------------------------------------------------------------------------

    public function test_decode_decompresses_gzip_body(): void
    {
        $original = 'hello opentelemetry';
        $compressed = gzencode($original);

        $response = $this->makeResponse(
            bodyContent: $compressed,
            contentLength: strlen($compressed),
            encoding: 'gzip',
        );

        $this->assertSame($original, PsrUtils::decode($response));
    }

    public function test_decode_throws_on_invalid_gzip_body(): void
    {
        $response = $this->makeResponse(
            bodyContent: 'not-valid-gzip',
            contentLength: 14,
            encoding: 'gzip',
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/gzip-decode/i');

        PsrUtils::decode($response);
    }

    // -------------------------------------------------------------------------
    // Integration: body larger than limit is truncated
    // -------------------------------------------------------------------------

    public function test_oversized_plain_body_is_truncated_to_max_bytes(): void
    {
        $oversized = str_repeat('A', ResponseBodySizeLimit::MAX_BYTES + 9999);

        // Simulate a stream that faithfully returns only what you ask for.
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getSize')->willReturn(null); // no Content-Length
        $stream->method('read')
               ->with(ResponseBodySizeLimit::MAX_BYTES)
               ->willReturn(substr($oversized, 0, ResponseBodySizeLimit::MAX_BYTES));

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);
        $response->method('getHeaderLine')->willReturn(''); // no Content-Encoding

        $result = PsrUtils::decode($response);

        $this->assertSame(ResponseBodySizeLimit::MAX_BYTES, strlen($result));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Build a minimal PSR-7 ResponseInterface stub.
     *
     * @param string      $bodyContent  Raw bytes the stream returns.
     * @param int|null    $contentLength Value returned by StreamInterface::getSize().
     *                                  0 means "empty", null means "unknown".
     * @param string|null $encoding     Value of Content-Encoding header, or null.
     */
    private function makeResponse(
        string $bodyContent,
        int|null $contentLength,
        string|null $encoding
    ): ResponseInterface {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('getSize')->willReturn($contentLength);

        if ($contentLength === 0) {
            // readBodyWithSizeLimit() bails out early; read() should not be called.
            $stream->expects($this->never())->method('read');
        } else {
            $readLimit = $contentLength !== null
                ? min($contentLength, ResponseBodySizeLimit::MAX_BYTES)
                : ResponseBodySizeLimit::MAX_BYTES;

            $stream->method('read')
                   ->with($readLimit)
                   ->willReturn($bodyContent);
        }

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);
        $response->method('getHeaderLine')
                 ->with('Content-Encoding')
                 ->willReturn($encoding ?? '');

        return $response;
    }
}
