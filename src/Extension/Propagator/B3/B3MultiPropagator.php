<?php

declare(strict_types=1);

namespace OpenTelemetry\Extension\Propagator\B3;

use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\SpanContextValidator;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\Propagation\ArrayAccessGetterSetter;
use OpenTelemetry\Context\Propagation\PropagationGetterInterface;
use OpenTelemetry\Context\Propagation\PropagationSetterInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

/**
 * B3Multi is a propagator that supports the specification for multiple
 * "b3" http headers used for trace context propagation across service
 * boundaries.
 * (https://github.com/openzipkin/b3-propagation#multiple-headers)
 */
final class B3MultiPropagator implements TextMapPropagatorInterface
{
    /**
     * The X-B3-TraceId header is required and is encoded as 32 or 16 lower-hex characters.
     * For example, a 128-bit TraceId header might look like: X-B3-TraceId: 463ac35c9f6413ad48485a3953bb6124 .
     * Unless propagating only the Sampling State, the X-B3-TraceId header is required.
     *
     * @see https://github.com/openzipkin/b3-propagation#traceid-1
     */
    private const TRACE_ID = 'X-B3-TraceId';

    /**
     * The X-B3-SpanId header is required and is encoded as 16 lower-hex characters.
     * For example, a SpanId header might look like: X-B3-SpanId: a2fb4a1d1a96d312 .
     * Unless propagating only the Sampling State, the X-B3-SpanId header is required.
     *
     * @see https://github.com/openzipkin/b3-propagation#spanid-1
     */
    private const SPAN_ID = 'X-B3-SpanId';

    /**
     * The X-B3-ParentSpanId header must be present on a child span and absent on the root span.
     * It is encoded as 16 lower-hex characters.
     * For example, a ParentSpanId header might look like: X-B3-ParentSpanId: 0020000000000001
     *
     * @see https://github.com/openzipkin/b3-propagation#parentspanid-1
     */
    private const PARENT_SPAN_ID = 'X-B3-ParentSpanId';

    /**
     * An accept sampling decision is encoded as X-B3-Sampled: 1 and a deny as X-B3-Sampled: 0.
     * Absent means defer the decision to the receiver of this header.
     * For example, a Sampled header might look like: X-B3-Sampled: 1
     *
     * Note: Before this specification was written, some tracers propagated X-B3-Sampled as true or false as opposed to 1 or 0.
     * While you shouldn't encode X-B3-Sampled as true or false, a lenient implementation may accept them.
     *
     * @see https://github.com/openzipkin/b3-propagation#sampling-state-1
     */
    private const SAMPLED = 'X-B3-Sampled';

    /**
     * Debug is encoded as X-B3-Flags: 1.
     * Absent or any other value can be ignored.
     * Debug implies an accept decision, so don't also send the X-B3-Sampled header.
     *
     * @see https://github.com/openzipkin/b3-propagation#debug-flag
     */
    private const DEBUG_FLAG = 'X-B3-Flags';

    private const IS_SAMPLED = '1';
    private const VALID_SAMPLED = [self::IS_SAMPLED, 'true'];
    private const IS_NOT_SAMPLED = '0';
    private const VALID_NON_SAMPLED = [self::IS_NOT_SAMPLED, 'false'];

    private const FIELDS = [
        self::TRACE_ID,
        self::SPAN_ID,
        self::PARENT_SPAN_ID,
        self::SAMPLED,
        self::DEBUG_FLAG,
    ];

    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (!self::$instance instanceof \OpenTelemetry\Extension\Propagator\B3\B3MultiPropagator) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /** {@inheritdoc} */
    public function fields(): array
    {
        return self::FIELDS;
    }

    /** {@inheritdoc} */
    public function inject(&$carrier, PropagationSetterInterface $setter = null, ContextInterface $context = null): void
    {
        $setter ??= ArrayAccessGetterSetter::getInstance();
        $context ??= Context::getCurrent();
        $spanContext = Span::fromContext($context)->getContext();

        if (!$spanContext->isValid()) {
            return;
        }

        // Inject multiple b3 headers
        $setter->set($carrier, self::TRACE_ID, $spanContext->getTraceId());
        $setter->set($carrier, self::SPAN_ID, $spanContext->getSpanId());

        $debugValue = $context->get(B3DebugFlagContextKey::instance());
        if ($debugValue && $debugValue === self::IS_SAMPLED) {
            $setter->set($carrier, self::DEBUG_FLAG, self::IS_SAMPLED);
        } else {
            $setter->set($carrier, self::SAMPLED, $spanContext->isSampled() ? self::IS_SAMPLED : self::IS_NOT_SAMPLED);
        }
    }

    public function extract($carrier, PropagationGetterInterface $getter = null, ContextInterface $context = null): ContextInterface
    {
        $getter ??= ArrayAccessGetterSetter::getInstance();
        $context ??= Context::getCurrent();

        $spanContext = self::extractImpl($carrier, $getter, $context);
        if (!$spanContext->isValid()) {
            return $context;
        }

        return $context->withContextValue(Span::wrap($spanContext));
    }

    private static function getSampledValue($carrier, PropagationGetterInterface $getter): ?int
    {
        $value = $getter->get($carrier, self::SAMPLED);

        if ($value === null) {
            return null;
        }

        if (in_array(strtolower($value), self::VALID_SAMPLED)) {
            return (int) self::IS_SAMPLED;
        }
        if (in_array(strtolower($value), self::VALID_NON_SAMPLED)) {
            return (int) self::IS_NOT_SAMPLED;
        }

        return null;
    }

    private static function extractImpl($carrier, PropagationGetterInterface $getter, ContextInterface &$context): SpanContextInterface
    {
        $traceId = $getter->get($carrier, self::TRACE_ID);
        $spanId = $getter->get($carrier, self::SPAN_ID);
        $sampled = self::getSampledValue($carrier, $getter);
        $debug = $getter->get($carrier, self::DEBUG_FLAG);

        if ($traceId === null || $spanId === null) {
            return SpanContext::getInvalid();
        }

        // B3 trace id may be 16 or 32 hex chars, but otel requires 32
        if (strlen($traceId) === 16) {
            $traceId = str_pad($traceId, 32, '0', STR_PAD_LEFT);
        }
        // Validates the traceId and spanId
        // Returns an invalid spanContext if any of the checks fail
        if (!SpanContextValidator::isValidTraceId($traceId) || !SpanContextValidator::isValidSpanId($spanId)) {
            return SpanContext::getInvalid();
        }

        if ($debug && $debug === self::IS_SAMPLED) {
            $context = $context->with(B3DebugFlagContextKey::instance(), self::IS_SAMPLED);
            $isSampled = SpanContextInterface::TRACE_FLAG_SAMPLED;
        } else {
            $isSampled = ($sampled === SpanContextInterface::TRACE_FLAG_SAMPLED);
        }

        // Only traceparent header is extracted. No tracestate.
        return SpanContext::createFromRemoteParent(
            $traceId,
            $spanId,
            $isSampled ? SpanContextInterface::TRACE_FLAG_SAMPLED : SpanContextInterface::TRACE_FLAG_DEFAULT
        );
    }
}
