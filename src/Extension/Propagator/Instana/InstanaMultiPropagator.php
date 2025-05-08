<?php

declare(strict_types=1);

namespace OpenTelemetry\Extension\Propagator\Instana;

use OpenTelemetry\API\Trace\NonRecordingSpan;
use OpenTelemetry\API\Trace\Span;
use OpenTelemetry\API\Trace\SpanContext;
use OpenTelemetry\API\Trace\SpanContextInterface;
use OpenTelemetry\API\Trace\TraceFlags;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\Propagation\ArrayAccessGetterSetter;
use OpenTelemetry\Context\Propagation\PropagationGetterInterface;
use OpenTelemetry\Context\Propagation\PropagationSetterInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

/**
 * InstanaPropagator is a propagator that supports the specification for multiple
 * "instana" http headers used for trace context propagation across service
 * boundaries.
 */
final class InstanaMultiPropagator implements TextMapPropagatorInterface
{
    /**
     * The  X-INSTANA-T header is required and is encoded as 32 lower-hex characters.
     * For example, a 128-bit TraceId header might look like: X-Instana-T: 463ac35c9f6413ad48485a3953bb6124 .
     *
     */
    private const INSTANA_TRACE_ID_HEADER = 'X-INSTANA-T';

    /**
     * The X-Instana-S header must be present on a child span and absent on the root span.
     * It is encoded as 16 lower-hex characters.
     * For example, a ParentSpanId header might look like: X-Instana-S: 0020000000000001
     *
     */
    private const INSTANA_SPAN_ID_HEADER = 'X-INSTANA-S';

    /**
     * An accept sampling decision is encoded as X-INSTANA-L: 1 and a deny as X-INSTANA-L: 0.
     * Absent means defer the decision to the receiver of this header.
     * For example, a Sampled header might look like: X-Instana-L: 1
     *
     */
    private const INSTANA_LEVEL_HEADER = 'X-INSTANA-L';

    private const IS_SAMPLED = '1';
    private const VALID_SAMPLED = [self::IS_SAMPLED, 'true'];
    private const IS_NOT_SAMPLED = '0';
    private const VALID_NON_SAMPLED = [self::IS_NOT_SAMPLED, 'false'];

    private const FIELDS = [
        self::INSTANA_TRACE_ID_HEADER,
        self::INSTANA_SPAN_ID_HEADER,
        self::INSTANA_LEVEL_HEADER,
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
    public function inject(&$carrier, ?PropagationSetterInterface $setter = null, ?ContextInterface $context = null): void
    {
        $setter ??= ArrayAccessGetterSetter::getInstance();
        $context ??= Context::getCurrent();
        $spanContext = Span::fromContext($context)->getContext();

        if (!$spanContext->isValid()) {
            return;
        }

        // Inject multiple Instana headers
        $setter->set($carrier, self::INSTANA_TRACE_ID_HEADER, $spanContext->getTraceId());
        $setter->set($carrier, self::INSTANA_SPAN_ID_HEADER, $spanContext->getSpanId());
        $setter->set($carrier, self::INSTANA_LEVEL_HEADER, $spanContext->isSampled() ? self::IS_SAMPLED : self::IS_NOT_SAMPLED);
    }

    public function extract($carrier, ?PropagationGetterInterface $getter = null, ?ContextInterface $context = null): ContextInterface
    {
        $getter ??= ArrayAccessGetterSetter::getInstance();
        $context ??= Context::getCurrent();

        $traceId = self::readHeader($carrier, $getter, self::INSTANA_TRACE_ID_HEADER);
        $spanId = self::readHeader($carrier, $getter, self::INSTANA_SPAN_ID_HEADER);
        $level = self::getSampledValue($carrier, $getter);

        $spanContext = self::extractImpl($carrier, $getter);

        if (($traceId === '' &&  $spanId === '') && $level !== null) {
            return (new NonRecordingSpan($spanContext))
                ->storeInContext($context);

        } elseif (!$spanContext->isValid()) {
            return $context;
        }

        return $context->withContextValue(Span::wrap($spanContext));

    }

    private static function readHeader($carrier, PropagationGetterInterface $getter, string $key): string
    {
        $header = $getter->get($carrier, $key) ?: '';

        // Return the header or an empty string if not found
        return $header;
    }
    private static function getSampledValue($carrier, PropagationGetterInterface $getter): ?int
    {
        $value = $getter->get($carrier, self::INSTANA_LEVEL_HEADER);

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

    private static function extractImpl($carrier, PropagationGetterInterface $getter): SpanContextInterface
    {
        $traceId = self::readHeader($carrier, $getter, self::INSTANA_TRACE_ID_HEADER);
        $spanId = self::readHeader($carrier, $getter, self::INSTANA_SPAN_ID_HEADER);
        $level = self::getSampledValue($carrier, $getter);

        if ($traceId && strlen($traceId) < 32) {
            $traceId = str_pad($traceId, 32, '0', STR_PAD_LEFT);
        }

        if ($spanId && strlen($spanId) < 16) {
            $spanId =  str_pad($spanId, 16, '0', STR_PAD_LEFT);
        }

        return SpanContext::createFromRemoteParent(
            $traceId,
            $spanId,
            $level ? TraceFlags::SAMPLED : TraceFlags::DEFAULT
        );

    }
}
