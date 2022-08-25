<?php

declare(strict_types=1);

namespace OpenTelemetry\Extension\Propagator\B3;

use function count;
use function explode;
use OpenTelemetry\API\Trace\AbstractSpan;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Propagation\ArrayAccessGetterSetter;
use OpenTelemetry\Context\Propagation\PropagationGetterInterface;
use OpenTelemetry\Context\Propagation\PropagationSetterInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

/**
 * B3 is a propagator that supports the specification for the B3 single
 * header "b3" used for trace context propagation across service boundaries.
 * (https://github.com/openzipkin/b3-propagation#single-header)
 */
final class B3SinglePropagator implements TextMapPropagatorInterface
{
    private const B3 = 'b3';

    private const IS_DEBUG = 'd';
    private const IS_SAMPLED = '1';
    private const VALID_SAMPLED = [self::IS_SAMPLED, 'true'];
    private const IS_NOT_SAMPLED = '0';
    private const VALID_NON_SAMPLED = [self::IS_NOT_SAMPLED, 'false'];

    private const FIELDS = [
        self::B3,
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
        $setter ??= ArrayAccessGetterSetter::getInstance();
        $context ??= Context::getCurrent();
        $spanContext = AbstractSpan::fromContext($context)->getContext();

        if (!$spanContext->isValid()) {
            return;
        }

        // Build and inject the b3 header
        $debugValue = $context->get(B3DebugFlagContextKey::instance());
        if ($debugValue) {
            $b3 = $spanContext->getTraceId() . '-' . $spanContext->getSpanId() . '-' . $debugValue;
        } elseif ($spanContext->isSampled()) {
            $b3 = $spanContext->getTraceId() . '-' . $spanContext->getSpanId() . '-' . self::IS_SAMPLED;
        } else {
            $b3 = $spanContext->getTraceId() . '-' . $spanContext->getSpanId() . '-' . self::IS_NOT_SAMPLED;
        }
        $setter->set($carrier, self::B3, $b3);
    }

    /** {@inheritdoc} */
    public function extract($carrier, PropagationGetterInterface $getter = null, Context $context = null): Context
    {
        $getter ??= ArrayAccessGetterSetter::getInstance();
        $context ??= Context::getCurrent();

        $spanContext = self::extractImpl($carrier, $getter, $context);
        if (!$spanContext->isValid()) {
            return $context;
        }

        return $context->withContextValue(AbstractSpan::wrap($spanContext));
    }

    private static function processSampledValue($value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (strtolower($value) === self::IS_DEBUG) {
            return (int) self::IS_SAMPLED;
        }

        if (in_array(strtolower($value), self::VALID_SAMPLED)) {
            return (int) self::IS_SAMPLED;
        }

        if (in_array(strtolower($value), self::VALID_NON_SAMPLED)) {
            return (int) self::IS_NOT_SAMPLED;
        }

        return null;
    }

    private static function extractImpl($carrier, PropagationGetterInterface $getter, Context &$context): SpanContextInterface
    {
        $b3 = $getter->get($carrier, self::B3);
        if ($b3 === null) {
            return SpanContext::getInvalid();
        }

        $pieces = explode('-', $b3);

        $traceId = null;
        $spanId = null;
        $samplingState = null;
        $parentSpanId = null;

        switch (count($pieces)) {
            case 1:
                // B3 = {deny-sampling-state}
                [$samplingState] = $pieces;

                break;
            case 2:
                // B3 = {trace-id}-{span-id}
                // Case of Defer sampling state; set it null so that default is used
                [$traceId, $spanId] = $pieces;

                break;
            case 3:
                // B3 = {trace-id}-{span-id}-{sampling-state}
                [$traceId, $spanId, $samplingState] = $pieces;

                break;
            case 4:
                // B3 = {trace-id}-{span-id}-{sampling-state}-{parent-span-id}
                [$traceId, $spanId, $samplingState, $parentSpanId] = $pieces;

                break;
            default:
                return SpanContext::getInvalid();
        }

        if ($traceId === null || $spanId === null) {
            return SpanContext::getInvalid();
        }

        // Validates the traceId and spanId
        // Returns an invalid spanContext if any of the checks fail
        if (!SpanContext::isValidTraceId($traceId) || !SpanContext::isValidSpanId($spanId)) {
            return SpanContext::getInvalid();
        }

        if ($samplingState && strtolower($samplingState) === self::IS_DEBUG) {
            $context = $context->with(B3DebugFlagContextKey::instance(), strtolower($samplingState));
        }

        $sampled = self::processSampledValue($samplingState);
        $isSampled = ($sampled === SpanContext::SAMPLED_FLAG);

        return SpanContext::createFromRemoteParent(
            $traceId,
            $spanId,
            $isSampled ? SpanContextInterface::TRACE_FLAG_SAMPLED : SpanContextInterface::TRACE_FLAG_DEFAULT
        );
    }
}
