<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK\Trace;

use OpenTelemetry\SDK\Trace\HttpBodyCapture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Sends real requests to the PHP built-in web server to verify that the body is recorded from the real
 * php://input and that the application can still read all of it afterwards.
 */
#[CoversClass(HttpBodyCapture::class)]
class HttpBodyCaptureServerTest extends TestCase
{
    private const DEFAULT = 'default';
    private const NO_POST_DATA_READING = 'enable_post_data_reading=0';

    /** @var array<string, resource> */
    private static array $servers = [];
    /** @var array<string, string> */
    private static array $baseUrls = [];

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('curl')) {
            self::markTestSkipped('ext-curl is required');
        }

        self::startServer(self::DEFAULT, []);
        self::startServer(self::NO_POST_DATA_READING, ['-d', 'enable_post_data_reading=0']);
    }

    /**
     * @param list<string> $phpOptions
     */
    private static function startServer(string $name, array $phpOptions): void
    {
        $probe = stream_socket_server('tcp://127.0.0.1:0');
        if ($probe === false) {
            self::markTestSkipped('Unable to find a free port');
        }
        $port = (int) parse_url('tcp://' . (string) stream_socket_get_name($probe, false), PHP_URL_PORT);
        fclose($probe);

        $command = [PHP_BINARY, ...$phpOptions, '-S', '127.0.0.1:' . $port, __DIR__ . '/fixtures/body_capture_app.php'];
        $process = proc_open(
            $command,
            [0 => ['pipe', 'r'], 1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']],
            $pipes,
            null,
            ['XDEBUG_MODE' => 'off', 'PATH' => (string) getenv('PATH')],
        );
        if (!is_resource($process)) {
            self::markTestSkipped('Unable to start the PHP built-in web server');
        }
        self::$servers[$name] = $process;
        self::$baseUrls[$name] = 'http://127.0.0.1:' . $port;

        for ($i = 0; $i < 100; $i++) {
            $socket = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.1);
            if ($socket !== false) {
                fclose($socket);

                return;
            }
            usleep(50_000);
        }
        self::tearDownAfterClass();
        self::markTestSkipped('The PHP built-in web server did not start in time');
    }

    #[\Override]
    public static function tearDownAfterClass(): void
    {
        foreach (self::$servers as $process) {
            proc_terminate($process);
            proc_close($process);
        }
        self::$servers = [];
    }

    /**
     * @param list<string> $headers
     */
    #[DataProvider('requestProvider')]
    public function test_body_is_recorded_without_consuming_it(string $server, string $body, array $headers, int $max, ?string $expectedCaptured): void
    {
        $response = $this->request($server, $body, array_merge($headers, ['Expect:']), $max);

        $this->assertSame($expectedCaptured, $response['captured']);
        $this->assertSame($body, base64_decode($response['application_body'], true), 'the application must still read the whole body');
    }

    /**
     * Every case is run against the default server and against one started with enable_post_data_reading=0:
     * for the content types that are recorded the result must not depend on that setting.
     */
    public static function requestProvider(): iterable
    {
        $long = str_repeat('a', 5000);
        $cases = [
            'json' => ['{"foo": "bar"}', ['Content-Type: application/json'], 1024, '{"foo": "bar"}'],
            'form urlencoded' => ['a=1&b=zażółć', ['Content-Type: application/x-www-form-urlencoded'], 1024, 'a=1&b=zażółć'],
            'text over the limit' => [$long, ['Content-Type: text/plain'], 1024, substr($long, 0, 1024)],
            'cut inside a multi-byte character' => ['zażółć', ['Content-Type: text/plain; charset=utf-8'], 4, 'zaż'],
            'larger than one read chunk' => [str_repeat('a', 20000), ['Content-Type: text/plain'], 20000, str_repeat('a', 20000)],
            'binary' => ["\x89PNG\r\n\x1a\n\x00\x01", ['Content-Type: application/octet-stream'], 1024, null],
            'gzip encoded' => [(string) gzencode('hello'), ['Content-Type: text/plain', 'Content-Encoding: gzip'], 1024, null],
            'empty body' => ['', ['Content-Type: text/plain'], 1024, null],
            'no content type' => ['hello', ['Content-Type:'], 1024, null],
        ];
        foreach ([self::DEFAULT, self::NO_POST_DATA_READING] as $server) {
            foreach ($cases as $name => [$body, $headers, $max, $expected]) {
                yield $server . ': ' . $name => [$server, $body, $headers, $max, $expected];
            }
        }
    }

    /**
     * multipart/form-data is not textual, so it is never recorded, whatever the PHP configuration is.
     * What differs is what the application sees: PHP parses the body into $_POST unless enable_post_data_reading=0,
     * in which case the raw body stays available in php://input.
     */
    public function test_multipart_body_is_not_recorded_with_default_settings(): void
    {
        $response = $this->request(self::DEFAULT, ['field' => 'value']);

        $this->assertNull($response['captured']);
        $this->assertSame(['field' => 'value'], $response['post_fields']);
        $this->assertSame('', base64_decode($response['application_body'], true), 'php://input is empty for multipart/form-data when PHP parses the body');
    }

    public function test_multipart_body_is_not_recorded_without_post_data_reading(): void
    {
        $response = $this->request(self::NO_POST_DATA_READING, ['field' => 'value']);

        $this->assertNull($response['captured']);
        $this->assertSame([], $response['post_fields']);
        $this->assertStringContainsString('name="field"', (string) base64_decode($response['application_body'], true));
    }

    /**
     * @param string|array<string, string> $body
     * @param list<string> $headers
     * @return array{captured: ?string, application_body: string, post_fields: array}
     */
    private function request(string $server, string|array $body, array $headers = [], int $max = 1024): array
    {
        $curl = curl_init(self::$baseUrls[$server] . '/?max=' . $max);
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        $result = curl_exec($curl);
        $this->assertNotFalse($result, curl_error($curl));
        curl_close($curl);

        return json_decode((string) $result, true, flags: JSON_THROW_ON_ERROR);
    }
}
