<?php

declare(strict_types=1);

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
                    ? Utils::leftZeroPad(Utils::decToHex($matches[2]))
                    : null,
                array_key_exists(3, $matches)
                    ? (int) ($matches[3] == '1')
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
            $ret .= '/' . Utils::hexToDec($context->getSpanId());
        }
        $ret .= ';o=' . ($context->isSampled() ? '1' : '0');

        return $ret;
    }
}
