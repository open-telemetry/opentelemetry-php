<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Configuration\Parser;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Configuration\Parser\MapParser
 */
class MapParserTest extends TestCase
{
    private const MAP_VALUES = [
        'empty' => [
            '',
            [],
        ],
        'single pair' => [
            'foo=bar',
            ['foo' => 'bar'],
        ],
        'multiple pairs' => [
            'foo=bar,bar=baz,baz=foo',
            ['foo' => 'bar', 'bar' => 'baz', 'baz' => 'foo'],
        ],
        'multiple items with spaces' => [
            'foo =bar,bar= baz, baz = foo',
            ['foo' => 'bar', 'bar' => 'baz', 'baz' => 'foo'],
        ],
        'base64 encoded value is split correctly and trailing equals sign is kept in value' => [
            'Authorization=Basic 1234abc=,bar=baz',
            ['Authorization' => 'Basic 1234abc=', 'bar' => 'baz'],
        ],
    ];

    private const INVALID_VALUES = [
        'string' => ['foobar'],
        'no equals' => ['foo=bar,barbaz'],
    ];

    /**
     * @dataProvider mapValueProvider
     */
    public function test_map_values_return_array(string $value, array $expected): void
    {
        $this->assertSame(
            \OpenTelemetry\SDK\Common\Configuration\Parser\MapParser::parse($value),
            $expected
        );
    }

    /**
     * @dataProvider invalidValueProvider
     */
    public function test_invalid_values_throw_exception(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        \OpenTelemetry\SDK\Common\Configuration\Parser\MapParser::parse($value);
    }

    public static function mapValueProvider(): array
    {
        return self::MAP_VALUES;
    }

    public static function invalidValueProvider(): array
    {
        return self::INVALID_VALUES;
    }
}
