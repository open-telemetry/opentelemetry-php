<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Extension\Propagator\CloudTrace;

use OpenTelemetry\Extension\Propagator\CloudTrace\Utils;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\Extension\Propagator\CloudTrace\Utils::class)]
class UtilsTest extends TestCase
{

    #[\PHPUnit\Framework\Attributes\DataProvider('for_test_left_zero_pad')]
    public function test_left_zero_pad(string $pad, int $howMuch, string $equalsTo) : void
    {
        $this->assertEquals(Utils::leftZeroPad($pad, $howMuch), $equalsTo, "Given leftZeroPad($pad, $howMuch) != $equalsTo");
    }

    public static function for_test_left_zero_pad() : array
    {
        return [
            ['a', 3, '00a'],
            ['aaa', 3, 'aaa'],
            ['aaa', 16, '0000000000000aaa'],
            ['', 1, '0'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('for_test_dec_to_hex')]
    public function test_dec_to_hex(string $decNum, string $equalsTo) : void
    {
        $this->assertEquals(Utils::decToHex($decNum), $equalsTo, "Given decToHex($decNum) != $equalsTo");
    }

    public static function for_test_dec_to_hex() : array
    {
        return [
            ['10', 'a'],
            ['1', '1'],
            ['9223372036854775807', '7fffffffffffffff'],
            ['18446744073709551615', 'ffffffffffffffff'],
            ['28446744073709551615', '18ac7230489e7ffff'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('for_test_hex_to_dec')]
    public function test_hex_to_dec(string $hexNum, string $equalsTo) : void
    {
        $this->assertEquals(Utils::hexToDec($hexNum), $equalsTo, "Given hexToDec($hexNum) != $equalsTo");
    }

    public static function for_test_hex_to_dec() : array
    {
        return [
            ['a', '10'],
            ['B', '11'],
            ['0xc', '12'],
            ['1', '1'],
            ['7fffffffffffffff', '9223372036854775807'],
            ['1ffffffffffffffA', '2305843009213693946'],
            ['1fffffffffffffff', '2305843009213693951'],
            ['8fffffffffffffff', '10376293541461622783'],
            ['7fffffffffffffff', '9223372036854775807'],
            ['8000000000000000', '9223372036854775808'],
            ['ffffffffffffffff', '18446744073709551615'],
            ['18ac7230489e7ffff', '28446744073709551615'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('for_test_is_big_num')]
    public function test_is_big_num($num, bool $equalsTo) : void
    {
        $this->assertEquals(Utils::isBigNum($num), $equalsTo, "Given isBigNum($num) != $equalsTo");
    }

    public static function for_test_is_big_num() : array
    {
        return [
            [-100.5, false],
            [-1, false],
            [1, false],
            [100.5, false],
            [9223372036854775806, false],
            [9223372036854775807, true],
            [9223372036854775808, true],
            [18446744073709551615, true],
            [28446744073709551615, true],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('for_test_base_convert')]
    public function test_base_convert(string $num, int $fromBase, int $toBase, string $equalsTo) : void
    {
        $result = Utils::baseConvert($num, $fromBase, $toBase);
        $this->assertEquals($result, $equalsTo, "Given baseConvert($num, $fromBase, $toBase) != $equalsTo (result=$result)");
    }

    public static function for_test_base_convert() : array
    {
        return [
            ['b', 16, 10, '11'],
            ['c', 16, 10, '12'],
            ['fffffffffffff', 16, 10, '4503599627370495'],
            ['7fffffff', 16, 10, '2147483647'],
            ['7ffffffffffffffe', 16, 10, '9223372036854775806'],
            ['7fffffffffffffff', 16, 10, '9223372036854775807'],
            ['8000000000000000', 16, 10, '9223372036854775808'], // bigger than signed int max 64 bit
            ['18ac7230489e7ffff', 16, 10, '28446744073709551615'],
            ['28446744073709551615', 10, 16, '18ac7230489e7ffff'],
            ['10', 10, 16, 'a'],
        ];
    }
}
