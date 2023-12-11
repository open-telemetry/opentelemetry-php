<?php

declare(strict_types=1);

namespace OpenTelemetry\Extension\Propagator\Jaeger;

use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\SpanContextValidator;
use OpenTelemetry\API\Trace\TraceFlags;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\Propagation\ArrayAccessGetterSetter;
use OpenTelemetry\Context\Propagation\PropagationGetterInterface;
use OpenTelemetry\Context\Propagation\PropagationSetterInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

/**
 * JaegerPropagator is a propagator that supports the specification for the header
 * "uber-trace-id" used for trace context propagation across service boundaries.
 * (https://www.jaegertracing.io/docs/1.52/client-libraries/#propagation-format)
 */
class JaegerPropagator implements TextMapPropagatorInterface
{
    private const TRACE_ID = 'uber-trace-id';

    private const IS_SAMPLED = '1';
    private const IS_NOT_SAMPLED = '0';
    private const DEFAULT_PARENT_SPAN_ID = '0';

    private const FIELDS = [
        self::TRACE_ID,
    ];

    private static ?TextMapPropagatorInterface $instance = null;

    public static function getInstance(): TextMapPropagatorInterface
    {
        if (self::$instance === null) {
            self::$instance = new JaegerPropagator();
        }

        return self::$instance;
    }

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

        $traceId = sprintf(
            '%s:%s:%s:%s',
            $spanContext->getTraceId(),
            $spanContext->getSpanId(),
            self::DEFAULT_PARENT_SPAN_ID,
            $spanContext->isSampled() ? self::IS_SAMPLED : self::IS_NOT_SAMPLED
        );

        $setter->set($carrier, self::TRACE_ID, $traceId);
    }

    /** {@inheritdoc} */
    public function extract($carrier, PropagationGetterInterface $getter = null, ContextInterface $context = null): ContextInterface
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
        $headerValue = $getter->get($carrier, self::TRACE_ID);

        if ($headerValue === null) {
            return SpanContext::getInvalid();
        }

        $pieces = explode(':', $headerValue);

        if (count($pieces) != 4) {
            return SpanContext::getInvalid();
        }

        [$traceId, $spanId, $parentSpanId, $traceFlags] = $pieces;

        // Jaeger trace id may be 32 characters and 0-pad them on the left
        if (strlen($traceId) < 32) {
            $traceId = str_pad($traceId, 32, '0', STR_PAD_LEFT);
        }

        // Jaeger span id than 16 characters and 0-pad them on the left
        if (strlen($spanId) < 16) {
            $spanId = str_pad($spanId, 16, '0', STR_PAD_LEFT);
        }

        if (!SpanContextValidator::isValidTraceId($traceId) || !SpanContextValidator::isValidSpanId($spanId)) {
            return SpanContext::getInvalid();
        }

        $isSampled = ((int) $traceFlags) == 1;

        return SpanContext::createFromRemoteParent(
            $traceId,
            $spanId,
            $isSampled ? TraceFlags::SAMPLED : TraceFlags::DEFAULT
        );
    }
}
