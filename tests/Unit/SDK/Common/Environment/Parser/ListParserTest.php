<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Environment\Parser;

use OpenTelemetry\SDK\Common\Environment\Parser\ListParser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Environment\Parser\ListParser
 */
class ListParserTest extends TestCase
{
    private const LIST_VALUES = [
        'empty' => [
            '',
            [],
        ],
        'single item' => [
            'foo',
            ['foo'],
        ],
        'two items' => [
            'foo,bar',
            ['foo', 'bar'],
        ],
        'multiple items' => [
            'foo,bar,faz,baz',
            ['foo', 'bar', 'faz', 'baz'],
        ],
        'multiple items with spaces' => [
            'foo, bar , faz, baz',
            ['foo', 'bar', 'faz', 'baz'],
        ],
    ];

    /**
     * @dataProvider listValueProvider
     */
    public function test_comma_separated_list_returns_array(string $value, array $expected): void
    {
        $this->assertSame(
            ListParser::parse($value),
            $expected
        );
    }

    public function listValueProvider(): array
    {
        return self::LIST_VALUES;
    }
}
