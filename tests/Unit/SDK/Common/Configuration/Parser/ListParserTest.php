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

    #[DataProvider('listValueProvider')]
    public function test_comma_separated_list_returns_array(string $value, array $expected): void
    {
        $this->assertSame(
            ListParser::parse($value),
            $expected
        );
    }

    public static function listValueProvider(): array
    {
        return self::LIST_VALUES;
    }
}
