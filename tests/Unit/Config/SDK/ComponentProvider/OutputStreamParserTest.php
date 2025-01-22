<?php

declare(strict_types=1);

namespace Unit\Config\SDK\ComponentProvider;

use OpenTelemetry\Config\SDK\ComponentProvider\OutputStreamParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @psalm-suppress InternalClass,InternalMethod
 */
#[CoversClass(OutputStreamParser::class)]
class OutputStreamParserTest extends TestCase
{
    #[DataProvider('parseProvider')]
    public function test_parse(string $input, string $expected): void
    {
        $this->assertSame($expected, OutputStreamParser::parse($input));
    }

    public static function parseProvider(): array
    {
        return [
            'stdout' => ['stdout', 'php://stdout'],
            'absolute' => ['file:///tmp/file.jsonl', '/tmp/file.jsonl'],
            'relative' => ['file://file.jsonl', 'file.jsonl'],
        ];
    }

    #[DataProvider('invalidParseProvider')]
    public function test_parse_exception(string $input): void
    {
        $this->expectException(\InvalidArgumentException::class);
        OutputStreamParser::parse($input);
    }

    public static function invalidParseProvider(): array
    {
        return [
            'invalid' => ['invalid'],
            'no scheme' => ['/var/log/file.jsonl'],
            'unknown scheme' => ['unknown://file.jsonl'],
        ];
    }
}
