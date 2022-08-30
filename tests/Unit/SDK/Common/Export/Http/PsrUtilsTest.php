<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Export\Http;

use function date;
use const DATE_RFC7231;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Utils;
use function gzdecode;
use function gzencode;
use Nyholm\Psr7\Response;
use OpenTelemetry\SDK\Common\Export\Http\PsrUtils;
use PHPUnit\Framework\TestCase;
use function time;
use UnexpectedValueException;

/**
 * @covers \OpenTelemetry\SDK\Common\Export\Http\PsrUtils
 */
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
        /** @psalm-suppress UndefinedFunction */
        $stream = Utils::streamFor('abc');
        $stream = PsrUtils::encodeStream($stream, 'gzip', new HttpFactory());
        $this->assertSame('abc', gzdecode((string) $stream));
    }

    public function test_decode_stream(): void
    {
        $stream = Utils::streamFor(gzencode('abc'));
        $stream = PsrUtils::decodeStream($stream, 'gzip', new HttpFactory());
        $this->assertSame('abc', (string) $stream);
    }

    public function test_encode_stream_unknown_encoding(): void
    {
        $this->expectException(UnexpectedValueException::class);

        PsrUtils::encodeStream(Utils::streamFor(), 'invalid', new HttpFactory());
    }

    public function test_decode_stream_unknown_encoding(): void
    {
        $this->expectException(UnexpectedValueException::class);

        PsrUtils::decodeStream(Utils::streamFor(), 'invalid', new HttpFactory());
    }
}
