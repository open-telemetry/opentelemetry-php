<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Export\Http;

use function array_shift;
use function date;
use const DATE_RFC7231;
use GuzzleHttp\Psr7\FnStream;
use GuzzleHttp\Psr7\PumpStream;
use function gzcompress;
use function gzdecode;
use function gzencode;
use Nyholm\Psr7\Response;
use OpenTelemetry\SDK\Common\Export\Http\PsrUtils;
use OpenTelemetry\SDK\Common\Export\Http\ResponseBodyTooLargeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function random_bytes;
use RuntimeException;
use function str_repeat;
use function str_split;
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
        $data = str_split('abcdef');
        // PumpStream returns at most one byte per call here, as a short read
        $stream = new PumpStream(static function (int $length) use (&$data) {
            return array_shift($data) ?? false;
        });

        $this->assertSame('abcdef', PsrUtils::readBody(new Response(200, [], $stream), [], 10));
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
        $this->assertSame($value, PsrUtils::readBody(new Response(200, [], gzcompress($value)), ['deflate'], 30));
    }

    #[DataProvider('compressors')]
    public function test_read_body_decoded_over_limit(string $encoding, callable $compress): void
    {
        $this->expectException(ResponseBodyTooLargeException::class);
        // the compressed body is below the limit, so only the decoded output can exceed it
        $body = $compress(str_repeat('a', 100_000));
        $this->assertLessThan(1_000, strlen($body));

        PsrUtils::readBody(new Response(200, [], $body), [$encoding], 1_000);
    }

    public static function compressors(): iterable
    {
        yield 'gzip' => ['gzip', gzencode(...)];
        yield 'deflate' => ['deflate', gzcompress(...)];
    }

    public function test_read_body_invalid_encoded_body_throws(): void
    {
        $this->expectException(UnexpectedValueException::class);
        PsrUtils::readBody(new Response(200, [], 'not gzip'), ['gzip'], 100);
    }

    public function test_read_body_unknown_encoding_throws(): void
    {
        $this->expectException(UnexpectedValueException::class);
        PsrUtils::readBody(new Response(200, [], 'foo'), ['invalid'], 100);
    }

    public function test_read_body_multiple_chunks_round_trip(): void
    {
        $value = random_bytes(20_000);

        $this->assertSame($value, PsrUtils::readBody(new Response(200, [], gzencode($value)), ['gzip'], 30_000));
        $this->assertSame($value, PsrUtils::readBody(new Response(200, [], gzcompress($value)), ['deflate'], 30_000));
    }

    #[DataProvider('compressors')]
    public function test_read_body_truncated_encoded_body_throws(string $encoding, callable $compress): void
    {
        $body = $compress(random_bytes(20_000));

        $this->expectException(UnexpectedValueException::class);
        PsrUtils::readBody(new Response(200, [], substr($body, 0, -10)), [$encoding], 30_000);
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
