<?php

declare(strict_types=1);

namespace OpenTelemetry\Extension\Propagator\CloudTrace;

/**
 * This class contains utilities that are used by the CloudTracePropagator.
 * This class mostly contains numerical handling functions to work with
 * trace and span IDs.
 */
final class Utils
{

    /**
     * Pads the string with zero string characters on left hand side, to max total string size.
     *
     * @param string $str The string to pad.
     * @param int $amount Total String size, default is 16.
     * @return string The padded string
     */
    public static function leftZeroPad(string $str, int $amount = 16) : string
    {
        return str_pad($str, $amount, '0', STR_PAD_LEFT);
    }

    /**
     * Converts a decimal number in string format to a hex number in string format.
     * The returned number will not start with 0x.
     *
     * @param string $num The number to convert.
     * @return string The converted number.
     */
    public static function decToHex(string $num) : string
    {
        $int = (int) $num;
        if (self::isBigNum($int)) {
            return self::baseConvert($num, 10, 16);
        }

        return dechex($int);
    }

    /**
     * Converts a hex number in string format to a decimal number in string format.
     * The given number does not have to start with 0x.
     *
     * @param string $num The number to convert.
     * @return string The converted number.
     */
    public static function hexToDec(string $num) : string
    {
        $dec = hexdec($num);
        if (self::isBigNum($dec)) {
            return self::baseConvert($num, 16, 10);
        }

        return (string) $dec;
    }

    /**
     * Tests whether the given number is larger than the maximum integer of the installed PHP's build.
     * On 32-bit system it's 2147483647 and on 64-bit it's 9223372036854775807.
     * We are comparing with >= and no >, because this function is used in context of what method to use
     * to convert to some base (in our case hex to octal and vice versa).
     * So it's ok if we use >=, because it means that only for MAX_INT we will use the slower baseConvert
     * method.
     *
     * @param int|float $number The number to test.
     * @return bool Whether it was bigger or not than the max.
     */
    public static function isBigNum($number) : bool
    {
        return $number >= PHP_INT_MAX;
    }

    /**
     * Custom function to convert a number in string format from one base to another.
     * Built-in functions, specifically for hex, do not work well in PHP under
     * all versions (32/64-bit) or if the number only fits into an unsigned long.
     * PHP does not have unsigned longs, so this function is necessary.
     *
     * @param string $num The number to convert (in some base).
     * @param int $fromBase The base to convert from.
     * @param int $toBase The base to convert to.
     * @return string Converted number in the new base.
     */
    public static function baseConvert(string $num, int $fromBase, int $toBase) : string
    {
        $num = strtolower($num);
        $chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        $newstring = substr($chars, 0, $toBase);

        $length = strlen($num);
        $result = '';

        $number = [];
        for ($i = 0; $i < $length; $i++) {
            $number[$i] = strpos($chars, $num[$i]);
        }

        do {
            $divide = 0;
            $newlen = 0;
            for ($i = 0; $i < $length; $i++) {
                if (!isset($number[$i]) || $number[$i] === false) {
                    return '';
                }
                $divide = $divide * $fromBase + $number[$i];
                if ($divide >= $toBase) {
                    $number[$newlen++] = (int) ($divide / $toBase);
                    $divide %= $toBase;
                } elseif ($newlen > 0) {
                    $number[$newlen++] = 0;
                }
            }
            $length = $newlen;
            $result = $newstring[$divide] . $result;
        } while ($newlen != 0);

        return $result;
    }
}
