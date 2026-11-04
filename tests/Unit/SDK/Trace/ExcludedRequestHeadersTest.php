<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace;

use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Trace\ExcludedRequestHeaders;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExcludedRequestHeaders::class)]
final class ExcludedRequestHeadersTest extends TestCase
{
    use TestState;

    /**
     * @param list<string> $entries
     * @param array<string, string|string[]> $headers
     */
    #[DataProvider('provideMatches')]
    public function test_matches(array $entries, array $headers, bool $expected): void
    {
        $this->assertSame($expected, (new ExcludedRequestHeaders($entries))->matches($headers));
    }

    public static function provideMatches(): iterable
    {
        yield 'no rules' => [[], ['X-Skip' => ['1']], false];
        yield 'exact value' => [['X-Skip: true'], ['X-Skip' => ['true']], true];
        yield 'different value' => [['X-Skip: true'], ['X-Skip' => ['false']], false];
        yield 'name is case-insensitive' => [['x-skip: true'], ['X-SKIP' => ['true']], true];
        yield 'underscore in name' => [['X_Skip_Me: 1'], ['X-Skip-Me' => ['1']], true];
        yield 'value is case-insensitive' => [['X-Skip: TRUE'], ['X-Skip' => ['true']], true];
        yield 'case-sensitive value' => [['X-Skip: (?-i)TRUE'], ['X-Skip' => ['true']], false];
        yield 'wildcard value' => [['User-Agent: kube-probe*'], ['User-Agent' => ['kube-probe/1.28']], true];
        yield 'name only, present' => [['X-Skip'], ['X-Skip' => ['anything']], true];
        yield 'name only with colon, present' => [['X-Skip:'], ['X-Skip' => ['']], true];
        yield 'name only, absent' => [['X-Skip'], ['Accept' => ['*/*']], false];
        yield 'any of several values' => [['X-Skip: b'], ['X-Skip' => ['a', 'b']], true];
        yield 'joined values need a wildcard' => [['X-Skip: b'], ['X-Skip' => 'a, b'], false];
        yield 'joined values with a wildcard' => [['X-Skip: *b*'], ['X-Skip' => 'a, b'], true];
        yield 'any of several rules' => [['X-A: 1', 'X-B: 2'], ['X-B' => ['2']], true];
        yield 'value with colon' => [['Referer: http://a/*'], ['Referer' => ['http://a/b']], true];
        yield 'whitespace is trimmed' => [['  X-Skip :  true  '], ['X-Skip' => ['true']], true];
        yield 'empty entry ignored' => [['', ':x'], ['X-Skip' => ['x']], false];
        yield 'plain string value' => [['X-Skip: 1'], ['X-Skip' => '1'], true];
    }

    public function test_headers_from_server(): void
    {
        $this->assertSame(
            ['x-skip-me' => '1', 'user-agent' => 'kube-probe', 'content-type' => 'text/plain'],
            ExcludedRequestHeaders::headersFromServer([
                'REQUEST_URI' => '/x',
                'HTTP_X_SKIP_ME' => '1',
                'HTTP_USER_AGENT' => 'kube-probe',
                'CONTENT_TYPE' => 'text/plain',
                'HTTP_NOT_A_STRING' => ['a'],
            ]),
        );
    }

    public function test_from_configuration_reads_current_value_each_time(): void
    {
        $headers = ['X-Skip' => ['1']];
        $this->assertFalse(ExcludedRequestHeaders::fromConfiguration()->matches($headers));

        $this->setEnvironmentVariable(Variables::OTEL_PHP_EXCLUDED_REQUEST_HEADERS, 'X-Skip: 1');
        $this->assertTrue(ExcludedRequestHeaders::fromConfiguration()->matches($headers));

        $this->setEnvironmentVariable(Variables::OTEL_PHP_EXCLUDED_REQUEST_HEADERS, 'X-Skip: 2');
        $this->assertFalse(ExcludedRequestHeaders::fromConfiguration()->matches($headers));
    }
}
