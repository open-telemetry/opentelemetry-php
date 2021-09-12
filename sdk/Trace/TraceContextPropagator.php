<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use function count;
use function explode;
use function hexdec;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Trace as API;
use OpenTelemetry\Trace\PropagationGetter;
use OpenTelemetry\Trace\PropagationSetter;

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
final class TraceContextPropagator implements API\TextMapPropagator
{
    public const TRACEPARENT = 'traceparent';
    public const TRACESTATE = 'tracestate';
    private const VERSION = '00'; // Currently, only '00' is supported

    /** {@inheritdoc} */
    public static function fields(): array
    {
        return [self::TRACEPARENT, self::TRACESTATE];
    }

    /** {@inheritdoc} */
    public static function inject(&$carrier, Context $context = null, PropagationSetter $setter = null): void
    {
        $context = $context ?? Context::getCurrent();
        $setter = $setter ?? new PropagationMap();
        $spanContext = Span::fromContext($context)->getSpanContext();

        if (!$spanContext->isValid()) {
            return;
        }

        // Build and inject the traceparent header
        $traceparent = self::VERSION . '-' . $spanContext->getTraceId() . '-' . $spanContext->getSpanId() . '-' . ($spanContext->isSampled() ? '01' : '00');
        $setter->set($carrier, self::TRACEPARENT, $traceparent);

        // Build and inject the tracestate header
        // Spec says to avoid sending empty tracestate headers
        if ($tracestate = (string) $spanContext->getTraceState()) {
            $setter->set($carrier, self::TRACESTATE, $tracestate);
        }
    }

    /** {@inheritdoc} */
    public static function extract($carrier, Context $context = null, PropagationGetter $getter = null): Context
    {
        $context = $context ?? Context::getCurrent();
        $getter = $getter ?? new PropagationMap();

        $spanContext = self::extractImpl($carrier, $getter);
        if (!$spanContext->isValid()) {
            return $context;
        }

        return $context->with(Span::wrap($spanContext));
    }

    private static function extractImpl($carrier, PropagationGetter $getter): API\SpanContext
    {
        $traceparent = $getter->get($carrier, self::TRACEPARENT);
        if ($traceparent === null) {
            return SpanContext::getInvalid();
        }

        // Traceparent = {version}-{trace-id}-{parent-id}-{trace-flags}
        $pieces = explode('-', $traceparent);

        // Unable to extract traceparent. Expected 4 values
        if (count($pieces) !== 4) {
            return SpanContext::getInvalid();
        }

        [$version, $traceId, $spanId, $traceFlags] = $pieces;

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
