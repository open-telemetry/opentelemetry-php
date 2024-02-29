<?php

declare(strict_types=1);

namespace OpenTelemetry\Extension\Propagator\CloudTrace;

use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextInterface;

/**
 * This format using a human readable string encoding to propagate SpanContext.
 * The current format of the header is `<trace-id>[/<span-id>][;o=<options>]`.
 * The options are a bitmask of options. Currently the only option is the
 * least significant bit which signals whether the request was traced or not
 * (1 = traced, 0 = not traced).
 */
final class CloudTraceFormatter
{
    const CONTEXT_HEADER_FORMAT = '/([0-9a-fA-F]{32})(?:\/(\d+))?(?:;o=(\d+))?/';

    /**
     * Generate a SpanContext object from the Trace Context header
     *
     * @return SpanContextInterface
     */
    public static function deserialize(string $header) : SpanContextInterface
    {
        $matched = preg_match(self::CONTEXT_HEADER_FORMAT, $header, $matches);

        if (!$matched) {
            return SpanContext::getInvalid();
        }
        if (!array_key_exists(2, $matches) || empty($matches[2])) {
            return SpanContext::getInvalid();
        }
        if (!array_key_exists(3, $matches)) {
            return SpanContext::getInvalid();
        }

        return SpanContext::createFromRemoteParent(
            strtolower($matches[1]),
            Utils::leftZeroPad(Utils::decToHex($matches[2])),
            (int) ($matches[3] == '1')
        );
    }

    /**
     * Convert a SpanContextInterface to header string
     *
     * @return string
     */
    public static function serialize(SpanContextInterface $context) : string
    {
        $ret = $context->getTraceId();
        if ($context->getSpanId()) {
            $ret .= '/' . Utils::hexToDec($context->getSpanId());
        }

        return $ret . (';o=' . ($context->isSampled() ? '1' : '0'));
    }
}
