<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace\Propagation;

use function count;
use function explode;
use function hexdec;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\SpanContextValidator;
use OpenTelemetry\API\Trace\TraceFlags;
use OpenTelemetry\API\Trace\TraceState;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\Propagation\ArrayAccessGetterSetter;
use OpenTelemetry\Context\Propagation\PropagationGetterInterface;
use OpenTelemetry\Context\Propagation\PropagationSetterInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use function sprintf;

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
final class TraceContextPropagator implements TextMapPropagatorInterface
{
    public const TRACEPARENT = 'traceparent';
    public const TRACESTATE = 'tracestate';
    private const VERSION = 0x00; // Currently, only '00' is supported
    private const SUPPORTED_FLAGS = TraceFlags::SAMPLED | TraceFlags::RANDOM;

    public const FIELDS = [
        self::TRACEPARENT,
        self::TRACESTATE,
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
    #[\Override]
    public function fields(): array
    {
        return self::FIELDS;
    }

    /** {@inheritdoc} */
    #[\Override]
    public function inject(&$carrier, ?PropagationSetterInterface $setter = null, ?ContextInterface $context = null): void
    {
        $setter ??= ArrayAccessGetterSetter::getInstance();
        $context ??= Context::getCurrent();
        $spanContext = Span::fromContext($context)->getContext();

        if (!$spanContext->isValid()) {
            return;
        }

        // Build and inject the traceparent header
        $traceparent = sprintf(
            '%02x-%s-%s-%02x',
            self::VERSION,
            $spanContext->getTraceId(),
            $spanContext->getSpanId(),
            $spanContext->getTraceFlags() & self::SUPPORTED_FLAGS,
        );
        $setter->set($carrier, self::TRACEPARENT, $traceparent);

        // Build and inject the tracestate header
        // Spec says to avoid sending empty tracestate headers
        if (($tracestate = (string) $spanContext->getTraceState()) !== '') {
            $setter->set($carrier, self::TRACESTATE, $tracestate);
        }
    }

    /** {@inheritdoc} */
    #[\Override]
    public function extract($carrier, ?PropagationGetterInterface $getter = null, ?ContextInterface $context = null): ContextInterface
    {
        $getter ??= ArrayAccessGetterSetter::getInstance();
        $context ??= Context::getCurrent();

        $spanContext = self::extractImpl($carrier, $getter);
        if (!$spanContext->isValid()) {
            return $context;
        }

        return $context->withContextValue(Span::wrap($spanContext));
    }

    private static function extractImpl($carrier, PropagationGetterInterface $getter): SpanContextInterface
    {
        $traceparent = $getter->get($carrier, self::TRACEPARENT);
        if ($traceparent === null) {
            return SpanContext::getInvalid();
        }

        // traceParent = {version}-{trace-id}-{parent-id}-{trace-flags}
        $pieces = explode('-', $traceparent);

        // If the header does not have at least 4 pieces, it is invalid -- restart the trace.
        if (count($pieces) < 4) {
            return SpanContext::getInvalid();
        }

        [$version, $traceId, $spanId, $traceFlags] = $pieces;

        /**
         * Return invalid if:
         * - Version is invalid (not 2 char hex or 'ff')
         * - Trace version, trace ID, span ID or trace flag are invalid
         */
        if (!TraceContextValidator::isValidTraceVersion($version)
            || !SpanContextValidator::isValidTraceId($traceId)
            || !SpanContextValidator::isValidSpanId($spanId)
            || !TraceContextValidator::isValidTraceFlag($traceFlags)
        ) {
            return SpanContext::getInvalid();
        }

        // Return invalid if the trace version is not a future version but still has > 4 pieces.
        $versionIsFuture = hexdec($version) > self::VERSION;
        if (count($pieces) > 4 && !$versionIsFuture) {
            return SpanContext::getInvalid();
        }

        // Tracestate = 'Vendor1=Value1,...,VendorN=ValueN'
        $rawTracestate = $getter->get($carrier, self::TRACESTATE);
        $tracestate = $rawTracestate !== null
            ? new TraceState($rawTracestate)
            : null;

        return SpanContext::createFromRemoteParent(
            $traceId,
            $spanId,
            hexdec($traceFlags) & self::SUPPORTED_FLAGS,
            $tracestate,
        );
    }
}
