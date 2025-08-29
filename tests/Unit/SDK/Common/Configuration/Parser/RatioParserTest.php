<?php

declare(strict_types=1);

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Configuration\Parser;

use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Configuration\Parser\RatioParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RangeException;

#[CoversClass(RatioParser::class)]
class RatioParserTest extends TestCase
{
    private const RATIO_VALUES = [
        'min ' => ['0'],
        'max ' => ['1'],
        'mid ' => ['0.5'],
    ];

    private const NON_NUMERIC_VALUES = [
        'string' => ['foo'],
        'bool' => ['true'],
    ];

    private const OUT_OF_RANGE_VALUES = [
        'too low' => ['-0.1'],
        'too high' => ['1.1'],
    ];

    #[DataProvider('ratioValueProvider')]
    public function test_ratio_values_return_float(string $value): void
    {
        $this->assertIsFloat(
            RatioParser::parse($value)
        );
    }

    #[DataProvider('nonNumericValueProvider')]
    public function test_non_numeric_values_throw_exception(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        RatioParser::parse($value);
    }

    #[DataProvider('outOfRangeValueProvider')]
    public function test_out_of_range_values_throw_exception(string $value): void
    {
        $this->expectException(RangeException::class);

        RatioParser::parse($value);
    }

    public static function ratioValueProvider(): array
    {
        return self::RATIO_VALUES;
    }

    public static function nonNumericValueProvider(): array
    {
        return self::NON_NUMERIC_VALUES;
    }

    public static function outOfRangeValueProvider(): array
    {
        return self::OUT_OF_RANGE_VALUES;
    }
}
