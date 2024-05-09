<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Export\Http;

use function date;
use const DATE_RFC7231;
use function gzdecode;
use function gzencode;
use Nyholm\Psr7\Response;
use OpenTelemetry\SDK\Common\Export\Http\PsrUtils;
use PHPUnit\Framework\TestCase;
use function time;
use UnexpectedValueException;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Common\Export\Http\PsrUtils::class)]
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

    public function test_decode_stream(): void
    {
        $value = PsrUtils::decode(gzencode('abc'), ['gzip']);
        $this->assertSame('abc', $value);
    }

    public function test_encode_stream_unknown_encoding(): void
    {
        PsrUtils::encode('', ['invalid'], $appliedEncodings);
        $this->assertSame([], $appliedEncodings);
    }

    public function test_decode_stream_unknown_encoding(): void
    {
        $this->expectException(UnexpectedValueException::class);

        PsrUtils::decode('', ['invalid']);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('compressionProvider')]
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
}
