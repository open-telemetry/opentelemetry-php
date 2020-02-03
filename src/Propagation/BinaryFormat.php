<?php

declare(strict_types=1);

namespace OpenTelemetry\Propagation;

use OpenTelemetry\Context\SpanContext;

final class BinaryFormat
{
    private const BINARY_FORMAT = '00%s1%s2%s';

    /**
     * Serializes the given SpanContext into the on-the-wire binary representation.
     *
     * @param SpanContext $context
     * @return string
     */
    public function toBytes(SpanContext $context) : string
    {
        if (! $context->IsValid()) {
            return '';
        }

        return sprintf(
            static::BINARY_FORMAT,
            $context->getTraceId(),
            $context->getSpanId(),
            (int) $context->isSampled()
        );
    }

    /**
     * Deserializes a SpanContext from it's on-the-wire binary representation.
     *
     * @param string $serialized
     * @return SpanContext
     */
    public function fromBytes(string $serialized) : SpanContext
    {
        if (strlen($serialized) === 0) {
            return SpanContext::generate();
        }

        // trim off leading 0 character
        $serialized = substr($serialized, 1);

        // next 33 characters should be the trace ID prefixed with 0
        if (strlen($serialized) >= 33 && (int) $serialized[0] === 0) {
            $traceId = substr($serialized, 1, 32);
            $serialized = substr($serialized, 33);
        } else {
            return SpanContext::generate();
        }

        // next 17 characters should be the span ID prefixed with 1
        if (strlen($serialized) >= 17 && (int) $serialized[0] === 1) {
            $spanId = substr($serialized, 1, 16);
            $serialized = substr($serialized, 17);
        } else {
            return SpanContext::fork($traceId);
        }

        // next 2 characters should be the trace flags prefixed with 2
        if (strlen($serialized) >= 2 && (int) $serialized[0] === 2) {
            $traceFlags = (bool) $serialized[1];
        } else {
            $traceFlags = false;
        }

        if (($span = SpanContext::restore($traceId, $spanId, $traceFlags))->IsValid()) {
            return $span;
        }

        return SpanContext::generate();
    }
}
