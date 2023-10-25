<?php
/**
 * Copyright 2017 OpenCensus Authors
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenTelemetry\Extension\Propagator\XCloudTrace;

use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextInterface;

/**
 * This format using a human readable string encoding to propagate SpanContext.
 * The current format of the header is `<trace-id>[/<span-id>][;o=<options>]`.
 * The options are a bitmask of options. Currently the only option is the
 * least significant bit which signals whether the request was traced or not
 * (1 = traced, 0 = not traced).
 */
final class XCloudTraceFormatter
{
    const CONTEXT_HEADER_FORMAT = '/([0-9a-fA-F]{32})(?:\/(\d+))?(?:;o=(\d+))?/';

    /**
     * Generate a SpanContext object from the Trace Context header
     *
     * @param string $header
     * @return SpanContextInterface
     */
    public static function deserialize(string $header) : SpanContextInterface
    {
        if (preg_match(self::CONTEXT_HEADER_FORMAT, $header, $matches)) {
            return SpanContext::createFromRemoteParent(
                strtolower($matches[1]),
                array_key_exists(2, $matches) && !empty($matches[2])
                    ? self::decToHex($matches[2])
                    : null,
                array_key_exists(3, $matches)
                    ? (int)($matches[3] == '1')
                    : null
            );
        }
        return SpanContext::getInvalid();
    }

    /**
     * Convert a SpanContextInterface to header string
     *
     * @param SpanContextInterface $context
     * @return string
     */
    public static function serialize(SpanContextInterface $context) : string
    {
        $ret = $context->getTraceId();
        if ($context->getSpanId()) {
            $ret .= '/' . self::hexToDec($context->getSpanId());
        }
        $ret .= ';o=' . ($context->isSampled() ? '1' : '0');
        return $ret;
    }

    private static function decToHex(string $num) : string
    {
        $int = (int) $num;
        if (self::isBigNum($int)) {
            $ret = self::baseConvert($num, 10, 16);
        } else {
            $ret = dechex($int);
        }
        return str_pad($ret, 16, '0', STR_PAD_LEFT);
    }

    private static function hexToDec(string $num) : string
    {
        $dec = hexdec($num);
        if (self::isBigNum($dec)) {
            return self::baseConvert($num, 16, 10);
        }
        return strval($dec);
    }

    private static function isBigNum(int|float $number) : bool
    {
        return $number >= PHP_INT_MAX;
    }

    private static function baseConvert(string $num, int $fromBase, int $toBase) : string
    {
        $chars = "0123456789abcdefghijklmnopqrstuvwxyz";
        $newstring = substr($chars, 0, $toBase);

        $length = strlen($num);
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $number[$i] = strpos($chars, $num[$i]);
        }

        do {
            $divide = 0;
            $newlen = 0;
            for ($i = 0; $i < $length; $i++) {
                $divide = $divide * $fromBase + $number[$i];
                if ($divide >= $toBase) {
                    $number[$newlen++] = (int)($divide / $toBase);
                    $divide = $divide % $toBase;
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
