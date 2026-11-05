<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Export\Http;

use ArrayObject;
use function date;
use const DATE_RFC7231;
use ErrorException;
use GuzzleHttp\Psr7\FnStream;
use function gzdecode;
use function gzencode;
use Nyholm\Psr7\Response;
use OpenTelemetry\SDK\Common\Export\Http\PsrUtils;
use OpenTelemetry\SDK\Common\Export\Http\ResponseBodyTooLargeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function random_bytes;
use function restore_error_handler;
use RuntimeException;
use function set_error_handler;
use function str_repeat;
use function strlen;
use function substr;
use function time;
use UnexpectedValueException;

#[CoversClass(PsrUtils::class)]
final class PsrUtilsTest extends TestCase
{
    public function test_retry_delay_initial(): void
    {
        $delay = PsrUtils::retryDelay(0, 1000);
        $this->assertGreaterThanOrEqual(.5, $delay);
        $this->assertLessThanOrEqual(1, $delay);
    }

    public function test_retry_delay_nth(): void
    {
        $delay = PsrUtils::retryDelay(2, 1000);
        $this->assertGreaterThanOrEqual(2, $delay);
        $this->assertLessThanOrEqual(4, $delay);
    }

    public function test_retry_delay_response_without_retry_after(): void
    {
        $delay = PsrUtils::retryDelay(2, 1000, new Response());
        $this->assertGreaterThanOrEqual(2, $delay);
        $this->assertLessThanOrEqual(4, $delay);
    }

    public function test_retry_delay_response_with_invalid_retry_after(): void
    {
        $delay = PsrUtils::retryDelay(2, 1000, (new Response())->withHeader('Retry-After', 'invalid'));
        $this->assertGreaterThanOrEqual(2, $delay);
        $this->assertLessThanOrEqual(4, $delay);
    }

    public function test_retry_delay_respects_response_retry_after(): void
    {
        $delay = PsrUtils::retryDelay(2, 1000, (new Response())->withHeader('Retry-After', '6'));
        $this->assertGreaterThan(4, $delay);
    }

    public function test_retry_delay_respects_response_retry_after_date(): void
    {
        $delay = PsrUtils::retryDelay(2, 1000, (new Response())->withHeader('Retry-After', date(DATE_RFC7231, time() + 6)));
        $this->assertGreaterThan(4, $delay);
    }

    public function test_retry_delay_uses_exponential_backoff_if_exceeds_retry_after(): void
    {
        $delay = PsrUtils::retryDelay(2, 1000, (new Response())->withHeader('Retry-After', '2'));
        $this->assertGreaterThanOrEqual(2, $delay);
    }

    public function test_encode_stream(): void
    {
        $value = PsrUtils::encode('abc', ['gzip']);
        $this->assertSame('abc', gzdecode($value));
    }

    public function test_encode_stream_unknown_encoding(): void
    {
        PsrUtils::encode('', ['invalid'], $appliedEncodings);
        $this->assertSame([], $appliedEncodings);
    }

    #[DataProvider('compressionProvider')]
    public function test_resolve_compression($input, $expected): void
    {
        $this->assertSame($expected, PsrUtils::compression($input));
    }

    public static function compressionProvider(): array
    {
        return [
            ['gzip', ['gzip']],
            ['', []],
            ['gzip,br', ['gzip','br']],
            ['gzip , brotli', ['gzip','brotli']],
            [['gzip'], ['gzip']],
        ];
    }

    public function test_read_body_reads_in_chunks(): void
    {
        $data = 'abcdef';
        $state = new ArrayObject(['position' => 0]);
        // read() returns one byte per call, a short read as PSR-7 allows
        $stream = new FnStream([
            'getSize' => static fn () => null,
            'isSeekable' => static fn () => false,
            'eof' => static fn () => $state['position'] >= strlen($data),
            'read' => static function (int $length) use ($state, $data) {
                return $state['position'] < strlen($data) ? $data[$state['position']++] : '';
            },
        ]);

        $this->assertSame('abcdef', PsrUtils::readBody(new Response(200, [], $stream), [], 10));
    }

    public function test_read_body_of_unknown_size_over_limit_throws(): void
    {
        $this->expectException(ResponseBodyTooLargeException::class);
        PsrUtils::readBody(new Response(200, [], $this->unknownSizeStream(str_repeat('a', 20))), [], 10);
    }

    public function test_read_body_of_unknown_size_at_limit(): void
    {
        $data = str_repeat('a', 10);

        $this->assertSame($data, PsrUtils::readBody(new Response(200, [], $this->unknownSizeStream($data)), [], 10));
    }

    /**
     * A stream that does not know its size, and returns 3 bytes at a time, so that the limit has to be enforced
     * on what is read.
     */
    private function unknownSizeStream(string $data): FnStream
    {
        $state = new ArrayObject(['position' => 0]);

        return new FnStream([
            'getSize' => static fn () => null,
            'isSeekable' => static fn () => false,
            'eof' => static fn () => $state['position'] >= strlen($data),
            'read' => static function (int $length) use ($state, $data) {
                $chunk = substr($data, $state['position'], 3);
                $state['position'] += strlen($chunk);

                return $chunk;
            },
        ]);
    }

    public function test_read_body_at_and_over_limit(): void
    {
        $this->assertSame('abcd', PsrUtils::readBody(new Response(200, [], 'abcd'), [], 4));

        $this->expectException(ResponseBodyTooLargeException::class);
        PsrUtils::readBody(new Response(200, [], 'abcde'), [], 4);
    }

    public function test_read_body_decoded_at_limit(): void
    {
        $value = str_repeat('a', 30);
        $this->assertSame($value, PsrUtils::readBody(new Response(200, [], gzencode($value)), ['gzip'], 30));
    }

    public function test_read_body_decoded_over_limit(): void
    {
        $this->expectException(ResponseBodyTooLargeException::class);
        // the compressed body is below the limit, so only the decoded output can exceed it
        $body = gzencode(str_repeat('a', 100_000));
        $this->assertLessThan(1_000, strlen($body));

        PsrUtils::readBody(new Response(200, [], $body), ['gzip'], 1_000);
    }

    #[DataProvider('unsupportedEncodings')]
    public function test_read_body_only_decodes_gzip(string $encoding): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Not supported decompression encoding');
        PsrUtils::readBody(new Response(200, [], 'foo'), [$encoding], 100);
    }

    public static function unsupportedEncodings(): iterable
    {
        yield 'deflate' => ['deflate'];
        yield 'brotli' => ['br'];
        yield 'unknown' => ['invalid'];
    }

    public function test_read_body_invalid_encoded_body_throws(): void
    {
        $this->expectException(UnexpectedValueException::class);
        PsrUtils::readBody(new Response(200, [], 'not gzip'), ['gzip'], 100);
    }

    public function test_read_body_multiple_chunks_round_trip(): void
    {
        $value = random_bytes(20_000);

        $this->assertSame($value, PsrUtils::readBody(new Response(200, [], gzencode($value)), ['gzip'], 30_000));
    }

    public function test_read_body_truncated_encoded_body_throws(): void
    {
        $body = gzencode(random_bytes(20_000));

        $this->expectException(UnexpectedValueException::class);
        PsrUtils::readBody(new Response(200, [], substr($body, 0, -10)), ['gzip'], 30_000);
    }

    public function test_read_body_is_not_affected_by_the_application_error_handler(): void
    {
        set_error_handler(static function (int $errno, string $errstr): never {
            throw new ErrorException($errstr, 0, $errno);
        });

        try {
            PsrUtils::readBody(new Response(200, [], 'not gzip'), ['gzip'], 100);
            $this->fail('An exception was expected');
        } catch (UnexpectedValueException $e) {
            $this->assertStringContainsString('Failed to decode gzip response body', $e->getMessage());
        } finally {
            restore_error_handler();
        }
    }

    public function test_read_body_concatenated_gzip_members_throw(): void
    {
        // the second member would be dropped, and would not count against the limit
        $body = gzencode('abc') . gzencode(str_repeat('a', 100_000));

        $this->expectException(UnexpectedValueException::class);
        PsrUtils::readBody(new Response(200, [], $body), ['gzip'], 1_000);
    }

    public function test_read_body_trailing_data_after_gzip_throws(): void
    {
        $this->expectException(UnexpectedValueException::class);
        PsrUtils::readBody(new Response(200, [], gzencode('abc') . 'garbage'), ['gzip'], 1_000);
    }

    public function test_read_body_applies_limit_to_every_encoding_step(): void
    {
        $body = gzencode(gzencode(str_repeat('a', 1_000_000)));

        $this->expectException(ResponseBodyTooLargeException::class);
        PsrUtils::readBody(new Response(200, [], $body), ['gzip', 'gzip'], 100_000);
    }

    public function test_read_body_empty_body(): void
    {
        $this->assertSame('', PsrUtils::readBody(new Response(), ['gzip'], 10));
    }

    public function test_read_body_empty_read_before_eof_throws(): void
    {
        $stream = new FnStream([
            'getSize' => static fn () => null,
            'isSeekable' => static fn () => false,
            'read' => static fn () => '',
            'eof' => static fn () => false,
        ]);

        $this->expectException(RuntimeException::class);
        PsrUtils::readBody(new Response(200, [], $stream), [], 10);
    }

    public function test_read_body_rejects_known_oversized_size_without_reading(): void
    {
        $stream = new FnStream([
            'getSize' => static fn () => 100,
            'read' => fn () => $this->fail('body must not be read'),
        ]);

        $this->expectException(ResponseBodyTooLargeException::class);
        PsrUtils::readBody(new Response(200, [], $stream), [], 10);
    }
}
