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
    private const UBER_TRACE_ID_HEADER = 'uber-trace-id';

    private const IS_NOT_SAMPLED = 0;
    private const IS_SAMPLED = 1;
    private const IS_DEBUG = 2;
    private const DEFAULT_PARENT_SPAN_ID = 0;

    private const FIELDS = [
        self::UBER_TRACE_ID_HEADER,
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

        $flag = $this->getFlag($spanContext, $context);

        $uberTraceId = sprintf(
            '%s:%s:%d:%d',
            $spanContext->getTraceId(),
            $spanContext->getSpanId(),
            self::DEFAULT_PARENT_SPAN_ID,
            $flag
        );

        $setter->set($carrier, self::UBER_TRACE_ID_HEADER, $uberTraceId);
    }

    /** {@inheritdoc} */
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

    private function getFlag(SpanContextInterface $spanContext, ContextInterface $context): int
    {
        if ($spanContext->isSampled()) {
            if ($context->get(JaegerDebugFlagContextKey::instance())) {
                return self::IS_DEBUG | self::IS_SAMPLED;
            }

            return self::IS_SAMPLED;
        }

        return self::IS_NOT_SAMPLED;
    }

    private static function extractImpl($carrier, PropagationGetterInterface $getter, ContextInterface &$context): SpanContextInterface
    {
        $headerValue = $getter->get($carrier, self::UBER_TRACE_ID_HEADER);

        if ($headerValue === null) {
            return SpanContext::getInvalid();
        }

        $pieces = explode(':', $headerValue);

        if (count($pieces) != 4) {
            return SpanContext::getInvalid();
        }

        [$traceId, $spanId, $parentSpanId, $traceFlags] = $pieces;

        $traceId = str_pad($traceId, SpanContextValidator::TRACE_LENGTH, '0', STR_PAD_LEFT);
        $spanId = str_pad($spanId, SpanContextValidator::SPAN_LENGTH, '0', STR_PAD_LEFT);

        if (!SpanContextValidator::isValidTraceId($traceId) || !SpanContextValidator::isValidSpanId($spanId)) {
            return SpanContext::getInvalid();
        }

        if ((int) $traceFlags & self::IS_DEBUG) {
            $context = $context->with(JaegerDebugFlagContextKey::instance(), true);
        }

        $isSampled = ((int) $traceFlags) & 1;

        return SpanContext::createFromRemoteParent(
            $traceId,
            $spanId,
            $isSampled ? TraceFlags::SAMPLED : TraceFlags::DEFAULT
        );
    }
}
