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
        // Spec says to avoid sending empty tracestate headers
        if ($tracestate = (string) $context->getTraceState()) {
            $setter->set($carrier, self::TRACESTATE, $tracestate);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function extract($carrier, API\PropagationGetter $getter): API\SpanContext
    {
        $traceparent = $getter->get($carrier, self::TRACEPARENT);
        if ($traceparent === null) {
            return SpanContext::getInvalid();
        }

        // Traceparent = {version}-{trace-id}-{parent-id}-{trace-flags}
        $pieces = explode('-', $traceparent);

        // Unable to extract traceparent. Expected 4 values
        $piecesCount = count($pieces);
        if ($piecesCount !== 4) {
            return SpanContext::getInvalid();
        }

        $version = $pieces[0];
        $traceId = $pieces[1];
        $spanId = $pieces[2];
        $traceFlags = $pieces[3];

        // Validates the version, traceId, spanId and traceFlags
        // Returns an invalid spanContext if any of the checks fail
        if ($version !== self::VERSION || !SpanContext::isValidTraceId($traceId) ||
            !SpanContext::isValidSpanId($spanId) || !SpanContext::isValidTraceFlag($traceFlags)) {
            return SpanContext::getInvalid();
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
