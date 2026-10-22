<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Configuration\Parser;

use OpenTelemetry\SDK\Common\Configuration\Parser\ListParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ListParser::class)]
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
     * An array input is already a list of values, so it is reindexed and its members cast rather
     * than split — which is what lets {@see ListParser::parse()} promise `list<string>`.
     */
    private const ARRAY_VALUES = [
        'empty array' => [
            [],
            [],
        ],
        'list of strings' => [
            ['foo', 'bar'],
            ['foo', 'bar'],
        ],
        'associative keys are dropped' => [
            ['a' => 'foo', 'b' => 'bar'],
            ['foo', 'bar'],
        ],
        'gaps in numeric keys are reindexed' => [
            [3 => 'foo', 7 => 'bar'],
            ['foo', 'bar'],
        ],
        'non-string scalars are cast' => [
            [123, 4.5, true],
            ['123', '4.5', '1'],
        ],
    ];

    #[DataProvider('listValueProvider')]
    public function test_comma_separated_list_returns_array(string $value, array $expected): void
    {
        $this->assertSame(
            ListParser::parse($value),
            $expected
        );
    }

    #[DataProvider('arrayValueProvider')]
    public function test_array_returns_list_of_strings(array $value, array $expected): void
    {
        $this->assertSame(
            $expected,
            ListParser::parse($value),
        );
    }

    public static function listValueProvider(): array
    {
        return self::LIST_VALUES;
    }

    public static function arrayValueProvider(): array
    {
        return self::ARRAY_VALUES;
    }
}
