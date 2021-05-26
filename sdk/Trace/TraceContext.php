<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

/**
 * TraceContext is a propagator that supports the W3C Trace Context format
 * (https://www.w3.org/TR/trace-context/)
 *
 * This propagator will propagate the traceparent and tracestate headers to
 * guarantee traces are not broken. It is up to the users of this propagator
 * to choose if they want to participate in a trace by modifying the
 * traceparent header and relevant parts of the tracestate header containing
 * their proprietary information.
 */
final class TraceContext implements API\TextMapFormatPropagator
{
    public const TRACEPARENT = 'traceparent';
    public const TRACESTATE = 'tracestate';
    private const VERSION = '00'; // Currently only '00' is supported
    private const VALID_VERSION = '/^[0-9a-f]{2}$/';
    private const VALID_TRACEFLAGS = '/^[0-9a-f]{2}$/';

    /**
     * {@inheritdoc}
     */
    public static function fields(): array
    {
        return [self::TRACEPARENT, self::TRACESTATE];
    }

    /**
     * {@inheritdoc}
     */
    public static function inject(API\SpanContext $context, &$carrier, API\PropagationSetter $setter): void
    {
        // Build and inject the traceparent header
        $traceparent = self::VERSION . '-' . $context->getTraceId() . '-' . $context->getSpanId() . '-' . ($context->isSampled() ? '01' : '00');
        $setter->set($carrier, self::TRACEPARENT, $traceparent);

        // Build and inject the tracestate header
        $tracestate = $context->getTraceState();
        if ($tracestate !== null) {
            $tracestateStr = $tracestate->build();
            $setter->set($carrier, self::TRACESTATE, $tracestateStr ? $tracestateStr : '');
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function extract($carrier, API\PropagationGetter $getter): API\SpanContext
    {
        $traceparent = $getter->get($carrier, self::TRACEPARENT);
        if ($traceparent === null) {
            throw new \InvalidArgumentException('Traceparent not present');
        }

        // Traceparent = {version}-{trace-id}-{parent-id}-{trace-flags}
        $pieces = explode('-', $traceparent);

        $piecesCount = count($pieces);
        if ($piecesCount != 4) {
            throw new \InvalidArgumentException(
                sprintf('Unable to extract traceparent. Expected 4 values, got %d', $piecesCount)
            );
        }

        $version = $pieces[0];
        if ((preg_match(self::VALID_VERSION, $version) === 0) || ($version !== self::VERSION)) {
            throw new \InvalidArgumentException(
                sprintf('Only version 00 is supported, got %s', $version)
            );
        }

        $traceId = $pieces[1];
        if ((preg_match(SpanContext::VALID_TRACE, $traceId) === 0) || ($traceId === SpanContext::INVALID_TRACE)) {
            throw new \InvalidArgumentException(
                sprintf('TraceID must be exactly 16 bytes (32 chars) and at least one non-zero byte, got %s', $traceId)
            );
        }

        $spanId = $pieces[2];
        if ((preg_match(SpanContext::VALID_SPAN, $spanId) === 0) || ($spanId === SpanContext::INVALID_SPAN)) {
            throw new \InvalidArgumentException(
                sprintf('SpanID must be exactly 8 bytes (16 chars) and at least one non-zero byte, got %s', $spanId)
            );
        }

        $traceFlags = $pieces[3];
        if (preg_match(self::VALID_TRACEFLAGS, $traceFlags) === 0) {
            throw new \InvalidArgumentException(
                sprintf('TraceFlags must be exactly 1 bytes (1 char) representing a bit field, got %s', $traceFlags)
            );
        }

        // Only the sampled flag is extracted from the traceFlags (00000001)
        $convertedTraceFlags = hexdec($traceFlags);
        $isSampled = ($convertedTraceFlags & SpanContext::SAMPLED_FLAG) === SpanContext::SAMPLED_FLAG;

        // Tracestate = 'Vendor1=Value1,...,VendorN=ValueN'
        $rawTracestate = $getter->get($carrier, self::TRACESTATE);
        if ($rawTracestate !== null) {
            $tracestate = new TraceState($rawTracestate);

            return SpanContext::restore($traceId, $spanId, $isSampled, true, $tracestate);
        }

        // Only traceparent header is extracted. No tracestate.
        return SpanContext::restore($traceId, $spanId, $isSampled, true);
    }
}
