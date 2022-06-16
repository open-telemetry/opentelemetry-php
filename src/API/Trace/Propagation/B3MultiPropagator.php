<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace\Propagation;

use OpenTelemetry\API\Trace\AbstractSpan;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Propagation\ArrayAccessGetterSetter;
use OpenTelemetry\Context\Propagation\PropagationGetterInterface;
use OpenTelemetry\Context\Propagation\PropagationSetterInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

/**
 * B3Multi is a propagator that supports the specification for the header
 * "b3" used for trace context propagation across service boundaries.
 * (https://github.com/openzipkin/b3-propagation)
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
    public const TRACE_ID = 'X-B3-TraceId';

    /**
     * The X-B3-SpanId header is required and is encoded as 16 lower-hex characters.
     * For example, a SpanId header might look like: X-B3-SpanId: a2fb4a1d1a96d312 .
     * Unless propagating only the Sampling State, the X-B3-SpanId header is required.
     *
     * @see https://github.com/openzipkin/b3-propagation#spanid-1
     */
    public const SPAN_ID = 'X-B3-SpanId';

    /**
     * The X-B3-ParentSpanId header must be present on a child span and absent on the root span.
     * It is encoded as 16 lower-hex characters.
     * For example, a ParentSpanId header might look like: X-B3-ParentSpanId: 0020000000000001
     *
     * @see https://github.com/openzipkin/b3-propagation#parentspanid-1
     */
    public const PARENT_SPAN_ID = 'X-B3-ParentSpanId';

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
    public const SAMPLED = 'X-B3-Sampled';

    /**
     * Debug is encoded as X-B3-Flags: 1.
     * Absent or any other value can be ignored.
     * Debug implies an accept decision, so don't also send the X-B3-Sampled header.
     *
     * @see https://github.com/openzipkin/b3-propagation#debug-flag
     */
    public const DEBUG_FLAG = 'X-B3-Flags';

    private const IS_SAMPLED = '1';
    private const IS_NOT_SAMPLED = '0';

    public const FIELDS = [
        self::TRACE_ID,
        self::SPAN_ID,
        self::PARENT_SPAN_ID,
        self::SAMPLED,
        self::DEBUG_FLAG,
    ];

    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (null === self::$instance) {
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
    public function inject(&$carrier, PropagationSetterInterface $setter = null, Context $context = null): void
    {
        $setter = $setter ?? ArrayAccessGetterSetter::getInstance();
        $context = $context ?? Context::getCurrent();
        $spanContext = AbstractSpan::fromContext($context)->getContext();

        if (!$spanContext->isValid()) {
            return;
        }

        $setter->set($carrier, self::TRACE_ID, $spanContext->getTraceId());
        $setter->set($carrier, self::SPAN_ID, $spanContext->getSpanId());
        $setter->set($carrier, self::SAMPLED, $spanContext->isSampled() ? self::IS_SAMPLED : self::IS_NOT_SAMPLED);
    }

    public function extract($carrier, PropagationGetterInterface $getter = null, Context $context = null): Context
    {
        $getter = $getter ?? ArrayAccessGetterSetter::getInstance();
        $context = $context ?? Context::getCurrent();

        $spanContext = self::extractImpl($carrier, $getter);
        if (!$spanContext->isValid()) {
            return $context;
        }

        return $context->withContextValue(AbstractSpan::wrap($spanContext));
    }

    private static function getSampledValue($carrier, PropagationGetterInterface $getter): ?int
    {
        $value = $getter->get($carrier, self::SAMPLED);

        if ($value === null) {
            return null;
        }

        if ($value === '0' || $value === '1') {
            return (int) $value;
        }

        if (strtolower($value) === 'true') {
            return 1;
        }
        if (strtolower($value) === 'false') {
            return 0;
        }

        return null;
    }

    private static function extractImpl($carrier, PropagationGetterInterface $getter): SpanContextInterface
    {
        $traceId = $getter->get($carrier, self::TRACE_ID);
        $spanId = $getter->get($carrier, self::SPAN_ID);
        $sampled = self::getSampledValue($carrier, $getter);

        if ($traceId === null || $spanId === null) {
            return SpanContext::getInvalid();
        }

        // Validates the traceId, spanId and sampled
        // Returns an invalid spanContext if any of the checks fail
        if (!SpanContext::isValidTraceId($traceId) || !SpanContext::isValidSpanId($spanId)) {
            return SpanContext::getInvalid();
        }

        $isSampled = ($sampled === SpanContext::SAMPLED_FLAG);

        // Only traceparent header is extracted. No tracestate.
        return SpanContext::createFromRemoteParent(
            $traceId,
            $spanId,
            $isSampled ? SpanContextInterface::TRACE_FLAG_SAMPLED : SpanContextInterface::TRACE_FLAG_DEFAULT
        );
    }
}
