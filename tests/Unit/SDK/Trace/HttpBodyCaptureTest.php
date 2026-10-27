<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use Nyholm\Psr7\ServerRequest;
use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\API\Behavior\Internal\LogWriter\NoopLogWriter;
use OpenTelemetry\API\Trace\SpanInterface;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Trace\HttpBodyCapture;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

#[CoversClass(HttpBodyCapture::class)]
class HttpBodyCaptureTest extends TestCase
{
    use TestState;

    #[DataProvider('contentTypeProvider')]
    public function test_is_textual(string $contentType, bool $expected): void
    {
        $this->assertSame($expected, HttpBodyCapture::isTextual($contentType));
    }

    public static function contentTypeProvider(): array
    {
        return [
            'text/plain' => ['text/plain', true],
            'text with charset' => ['text/html; charset=utf-8', true],
            'upper case' => ['TEXT/Plain', true],
            'json' => ['application/json', true],
            'json suffix' => ['application/vnd.api+json', true],
            'json with parameters' => ['application/json; profile="x"', true],
            'xml' => ['application/xml', true],
            'xml suffix' => ['application/soap+xml', true],
            'yaml' => ['application/yaml', true],
            'yaml suffix' => ['application/x+yaml', true],
            'form' => ['application/x-www-form-urlencoded', true],
            'charset parameter' => ['application/custom; charset=utf-8', true],
            'octet-stream' => ['application/octet-stream', false],
            'fake charset inside a quoted value' => ['application/octet-stream; note="x;charset=utf-8"', false],
            'multipart with charset' => ['multipart/form-data; charset=utf-8; boundary=x', false],
            'fake charset after an escaped quote' => ['application/octet-stream; note="a\\"; charset=utf-8"', false],
            'charset after a quoted value' => ['application/octet-stream; note="a;b"; charset=utf-8', true],
            'image' => ['image/png', false],
            'multipart' => ['multipart/form-data; boundary=x', false],
            'empty' => ['', false],
        ];
    }

    public function test_extract_returns_body(): void
    {
        $request = self::request('{"foo": "bar"}', 'application/json');

        $this->assertSame('{"foo": "bar"}', HttpBodyCapture::extract($request, 1024));
    }

    public function test_extract_truncates_to_max_size(): void
    {
        $this->assertSame('hello', HttpBodyCapture::extract(self::request('hello world', 'text/plain'), 5));
    }

    public function test_extract_truncates_on_utf8_character_boundary(): void
    {
        // "zażółć": ż, ó, ł and ć take two bytes each; 4 bytes would cut "ż" (bytes 3-4) in half after "za"
        $request = self::request('zażółć', 'text/plain; charset=utf-8');

        $this->assertSame('za', HttpBodyCapture::extract($request, 3));
        $this->assertSame('zaż', HttpBodyCapture::extract($request, 4));
        $this->assertSame('zaż', HttpBodyCapture::extract($request, 5));
    }

    public function test_extract_does_not_move_the_stream_position(): void
    {
        $request = self::request('hello world', 'text/plain');
        $request->getBody()->seek(6);

        HttpBodyCapture::extract($request, 5);

        $this->assertSame(6, $request->getBody()->tell());
        $this->assertSame('world', $request->getBody()->getContents());
    }

    #[DataProvider('notRecordableProvider')]
    public function test_extract_returns_null_when_not_recordable(string $body, array $headers, int $maxSize): void
    {
        $request = new ServerRequest('POST', 'https://example.com/', $headers, $body);

        $this->assertNull(HttpBodyCapture::extract($request, $maxSize));
    }

    public static function notRecordableProvider(): array
    {
        return [
            'binary content type' => ["\x89PNG", ['Content-Type' => 'image/png'], 1024],
            'no content type' => ['hello', [], 1024],
            'empty body' => ['', ['Content-Type' => 'text/plain'], 1024],
            'gzip encoded' => ['hello', ['Content-Type' => 'text/plain', 'Content-Encoding' => 'gzip'], 1024],
            'non utf-8 charset' => ['hello', ['Content-Type' => 'text/plain; charset=iso-8859-2'], 1024],
            'invalid utf-8 despite text type' => ["abc\xff\xfe\xfd\xfc def", ['Content-Type' => 'text/plain'], 1024],
            'zero limit' => ['hello', ['Content-Type' => 'text/plain'], 0],
            'negative limit' => ['hello', ['Content-Type' => 'text/plain'], -1],
        ];
    }

    public function test_extract_accepts_identity_content_encoding(): void
    {
        $request = new ServerRequest('POST', 'https://example.com/', ['Content-Type' => 'text/plain', 'Content-Encoding' => 'identity'], 'hello');

        $this->assertSame('hello', HttpBodyCapture::extract($request, 1024));
    }

    public function test_extract_skips_non_seekable_stream(): void
    {
        $nonSeekable = $this->createMock(StreamInterface::class);
        $nonSeekable->method('isReadable')->willReturn(true);
        $nonSeekable->method('isSeekable')->willReturn(false);
        $request = (new ServerRequest('POST', 'https://example.com/', ['Content-Type' => 'text/plain']))->withBody($nonSeekable);

        $this->assertNull(HttpBodyCapture::extract($request, 1024));
    }

    public function test_extract_returns_null_for_unreadable_stream(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('isReadable')->willReturn(false);
        $stream->method('isSeekable')->willReturn(true);
        $stream->expects($this->never())->method('read');

        $this->assertNull(HttpBodyCapture::extract(self::requestWithBody($stream), 1024));
    }

    #[DataProvider('failingStreamMethodProvider')]
    public function test_extract_swallows_stream_exceptions(string $failingMethod): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('isReadable')->willReturn(true);
        $stream->method('isSeekable')->willReturn(true);
        $stream->method('tell')->willReturn(0);
        $stream->method('read')->willReturn('hello');
        $stream->method($failingMethod)->willThrowException(new \RuntimeException('stream failure'));

        $this->assertNull(HttpBodyCapture::extract(self::requestWithBody($stream), 1024));
    }

    public static function failingStreamMethodProvider(): array
    {
        return [
            'tell' => ['tell'],
            'rewind' => ['rewind'],
            'read' => ['read'],
        ];
    }

    public function test_extract_restores_the_position_when_reading_fails(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('isReadable')->willReturn(true);
        $stream->method('isSeekable')->willReturn(true);
        $stream->method('tell')->willReturn(7);
        $stream->method('read')->willThrowException(new \RuntimeException('stream failure'));
        $stream->expects($this->once())->method('seek')->with(7);

        $this->assertNull(HttpBodyCapture::extract(self::requestWithBody($stream), 1024));
    }

    public function test_extract_still_returns_the_body_when_the_position_cannot_be_restored(): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('isReadable')->willReturn(true);
        $stream->method('isSeekable')->willReturn(true);
        $stream->method('tell')->willReturn(0);
        $stream->method('read')->willReturn('hello');
        $stream->method('seek')->willThrowException(new \RuntimeException('stream failure'));
        Logging::setLogWriter(new NoopLogWriter());

        try {
            $this->assertSame('hello', HttpBodyCapture::extract(self::requestWithBody($stream), 5));
        } finally {
            Logging::reset();
        }
    }

    public function test_extract_warns_when_the_position_cannot_be_restored(): void
    {
        $state = new class() {
            /** @var list<array{mixed, string}> */
            public array $messages = [];
        };
        Logging::setLogWriter(new class($state) implements LogWriterInterface {
            public function __construct(private readonly object $state)
            {
            }

            #[\Override]
            public function write($level, string $message, array $context): void
            {
                $this->state->messages[] = [$level, $message];
            }
        });
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('isReadable')->willReturn(true);
        $stream->method('isSeekable')->willReturn(true);
        $stream->method('tell')->willReturn(0);
        $stream->method('read')->willReturn('hello');
        $stream->method('seek')->willThrowException(new \RuntimeException('stream failure'));

        try {
            $this->assertSame('hello', HttpBodyCapture::extract(self::requestWithBody($stream), 5));
        } finally {
            Logging::reset();
        }

        $this->assertSame([['warning', 'Unable to restore the request body stream position']], $state->messages);
    }

    public function test_extract_survives_a_failing_log_writer(): void
    {
        Logging::setLogWriter(new class() implements LogWriterInterface {
            #[\Override]
            public function write($level, string $message, array $context): void
            {
                throw new \ErrorException('log writer failure');
            }
        });
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('isReadable')->willReturn(true);
        $stream->method('isSeekable')->willReturn(true);
        $stream->method('tell')->willReturn(0);
        $stream->method('read')->willReturn('hello');
        $stream->method('seek')->willThrowException(new \RuntimeException('stream failure'));

        try {
            $this->assertSame('hello', HttpBodyCapture::extract(self::requestWithBody($stream), 5));
        } finally {
            Logging::reset();
        }
    }

    public function test_extract_treats_an_empty_read_as_the_end_of_the_body(): void
    {
        // like php://input, the stream does not report eof() until a read returns nothing
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('isReadable')->willReturn(true);
        $stream->method('isSeekable')->willReturn(true);
        $stream->method('tell')->willReturn(0);
        $stream->method('eof')->willReturn(false);
        $stream->method('read')->willReturnOnConsecutiveCalls('hello', '');

        $this->assertSame('hello', HttpBodyCapture::extract(self::requestWithBody($stream), 1024));
    }

    public function test_extract_accepts_a_limit_that_would_overflow(): void
    {
        $this->assertSame('hello', HttpBodyCapture::extract(self::request('hello', 'text/plain'), PHP_INT_MAX));
    }

    public function test_extract_reads_more_than_one_chunk(): void
    {
        // php://input returns at most 8192 bytes per read
        $body = str_repeat('a', 20000);
        $state = new \stdClass();
        $state->offset = 0;
        $state->largestRequest = 0;
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('isReadable')->willReturn(true);
        $stream->method('isSeekable')->willReturn(true);
        $stream->method('tell')->willReturn(0);
        $stream->method('rewind')->willReturnCallback(function () use ($state): void {
            $state->offset = 0;
        });
        $stream->method('eof')->willReturnCallback(function () use ($state, $body): bool {
            return $state->offset >= strlen($body);
        });
        $stream->method('read')->willReturnCallback(function (int $length) use ($state, $body): string {
            $state->largestRequest = max($state->largestRequest, $length);
            $chunk = substr($body, $state->offset, min($length, 8192));
            $state->offset += strlen($chunk);

            return $chunk;
        });

        $this->assertSame(str_repeat('a', 20000), HttpBodyCapture::extract(self::requestWithBody($stream), 20000));
        $this->assertSame(str_repeat('a', 10000), HttpBodyCapture::extract(self::requestWithBody($stream), 10000));
        // a large limit must not make the stream allocate that much at once
        $this->assertLessThanOrEqual(8192, $state->largestRequest);
    }

    public function test_extract_does_not_alter_an_invalid_body_that_was_not_cut(): void
    {
        // Latin-1 "café" declared as text without a charset
        $this->assertNull(HttpBodyCapture::extract(self::request("caf\xe9", 'text/plain'), 1024));
        // exactly at the limit, so it was not cut either
        $this->assertNull(HttpBodyCapture::extract(self::request("caf\xe9", 'text/plain'), 4));
    }

    public function test_extract_drops_only_the_character_that_the_limit_cut(): void
    {
        $this->assertSame('caf', HttpBodyCapture::extract(self::request('café', 'text/plain'), 4));
        $this->assertSame('café', HttpBodyCapture::extract(self::request('café', 'text/plain'), 5));
    }

    public function test_extract_rejects_malformed_bytes_near_the_limit(): void
    {
        // 0xFF can never be part of UTF-8: the body is not text, whatever the limit
        $this->assertNull(HttpBodyCapture::extract(self::request("abc\xffZ", 'text/plain'), 4));
        // a lone continuation byte, a lead byte followed by a wrong byte
        $this->assertNull(HttpBodyCapture::extract(self::request("abc\x80Z", 'text/plain'), 4));
        $this->assertNull(HttpBodyCapture::extract(self::request("ab\xe2\x28Z", 'text/plain'), 4));
        // overlong lead byte
        $this->assertNull(HttpBodyCapture::extract(self::request("abc\xc0Z", 'text/plain'), 4));
    }

    public function test_extract_drops_an_incomplete_three_and_four_byte_character(): void
    {
        $this->assertSame('ab', HttpBodyCapture::extract(self::request('ab€c', 'text/plain'), 4));
        $this->assertSame('ab€', HttpBodyCapture::extract(self::request('ab€c', 'text/plain'), 5));
        $this->assertSame('ab', HttpBodyCapture::extract(self::request('ab😀c', 'text/plain'), 5));
        $this->assertSame('ab😀', HttpBodyCapture::extract(self::request('ab😀c', 'text/plain'), 6));
    }

    public function test_extract_returns_null_when_the_limit_cuts_the_first_character(): void
    {
        $this->assertNull(HttpBodyCapture::extract(self::request('żółć', 'text/plain'), 1));
    }

    #[DataProvider('charsetHeaderProvider')]
    public function test_extract_handles_charset_parameter_formatting(string $contentType, ?string $expected): void
    {
        $this->assertSame($expected, HttpBodyCapture::extract(self::request('hello', $contentType), 1024));
    }

    public static function charsetHeaderProvider(): array
    {
        return [
            'upper case names' => ['Text/Plain; CHARSET=UTF-8', 'hello'],
            'spaces around' => ['text/plain ;  charset = utf-8 ', 'hello'],
            'quoted' => ['text/plain; charset="utf-8"', 'hello'],
            'utf8 alias' => ['text/plain; charset=utf8', 'hello'],
            'ascii' => ['text/plain; charset=US-ASCII', 'hello'],
            'other charset' => ['text/plain; charset=windows-1250', null],
            'fake charset inside a quoted value' => ['text/plain; note="x;charset=windows-1250"', 'hello'],
        ];
    }

    public function test_is_disabled_by_default(): void
    {
        $this->assertFalse(HttpBodyCapture::isEnabled());
    }

    public function test_capture_request_body_does_nothing_when_disabled(): void
    {
        $span = $this->createMock(SpanInterface::class);
        $span->expects($this->never())->method('setAttribute');

        HttpBodyCapture::captureRequestBody($span, self::request('hello', 'text/plain'));
    }

    public function test_capture_request_body_does_nothing_for_a_span_that_is_not_recording(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_INSTRUMENTATION_HTTP_SERVER_CAPTURE_REQUEST_BODY, 'true');
        $span = $this->createMock(SpanInterface::class);
        $span->method('isRecording')->willReturn(false);
        $span->expects($this->never())->method('setAttribute');
        $stream = $this->createMock(StreamInterface::class);
        $stream->expects($this->never())->method('read');

        HttpBodyCapture::captureRequestBody($span, self::requestWithBody($stream));
    }

    public function test_capture_request_body_sets_attribute_with_default_limit(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_INSTRUMENTATION_HTTP_SERVER_CAPTURE_REQUEST_BODY, 'true');
        $body = str_repeat('a', 2000);
        $span = $this->createMock(SpanInterface::class);
        $span->method('isRecording')->willReturn(true);
        $span->expects($this->once())
            ->method('setAttribute')
            ->with('http.request.body.content', str_repeat('a', 1024));

        HttpBodyCapture::captureRequestBody($span, self::request($body, 'text/plain'));
    }

    public function test_capture_request_body_honours_configured_limit(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_INSTRUMENTATION_HTTP_SERVER_CAPTURE_REQUEST_BODY, 'true');
        $this->setEnvironmentVariable(Variables::OTEL_PHP_INSTRUMENTATION_HTTP_SERVER_CAPTURE_REQUEST_BODY_MAX_SIZE, '3');
        $span = $this->createMock(SpanInterface::class);
        $span->method('isRecording')->willReturn(true);
        $span->expects($this->once())
            ->method('setAttribute')
            ->with('http.request.body.content', 'hel');

        HttpBodyCapture::captureRequestBody($span, self::request('hello', 'text/plain'));
    }

    public function test_capture_request_body_sets_nothing_for_binary_body(): void
    {
        $this->setEnvironmentVariable(Variables::OTEL_PHP_INSTRUMENTATION_HTTP_SERVER_CAPTURE_REQUEST_BODY, 'true');
        $span = $this->createMock(SpanInterface::class);
        $span->method('isRecording')->willReturn(true);
        $span->expects($this->never())->method('setAttribute');

        HttpBodyCapture::captureRequestBody($span, self::request("\x00\x01", 'application/octet-stream'));
    }

    private static function requestWithBody(StreamInterface $body): ServerRequest
    {
        return (new ServerRequest('POST', 'https://example.com/', ['Content-Type' => 'text/plain']))->withBody($body);
    }

    private static function request(string $body, string $contentType): ServerRequest
    {
        return new ServerRequest('POST', 'https://example.com/', ['Content-Type' => $contentType], $body);
    }
}
