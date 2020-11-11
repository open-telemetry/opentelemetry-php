<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

use OpenTelemetry\Trace as API;

final class TraceContext implements API\TextMapFormatPropagator
{
    public const TRACEPARENT = 'http_traceparent';
    public const TRACESTATE = 'http_tracestate';

    // TODO Consolidate these and the validity checks in SpanContext.php
    private const SUPPORTED_VERSION = '00';
    private const INVALID_TRACE = '00000000000000000000000000000000';
    private const INVALID_SPAN = '0000000000000000';
    private const VALID_VERSION = '/^[0-9a-f]{2}$/';
    private const VALID_TRACE = '/^[0-9a-f]{32}$/';
    private const VALID_SPAN = '/^[0-9a-f]{16}$/';
    private const VALID_TRACEFLAGS = '/^[0-9a-f]{2}$/';
    private const SAMPLED_FLAG = 1;

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
        $traceparent = self::SUPPORTED_VERSION . '-' . $context->getTraceId() . '-' . $context->getSpanId() . '-' . ($context->isSampled() ? '01' : '00');
        $setter->set($carrier, self::TRACEPARENT, $traceparent);
    }

    /**
     * {@inheritdoc}
     */
    public static function extract($carrier, API\PropagationGetter $getter): API\SpanContext
    {
        $traceparent = $getter->get($carrier, self::TRACEPARENT);
        if (null === $traceparent) {
            throw new \InvalidArgumentException('Traceparent not present');
        }

        // Traceparent = {version}-{trace-id}-{parent-id}-{trace-flags}
        $pieces = explode('-', $traceparent);

        $peicesCount = count($pieces);
        if ($peicesCount != 4) {
            throw new \InvalidArgumentException('Unable to extract traceparent. Contains Invalid values');
        }

        // Parse the traceparent version. Currently only '00' is supported.
        $version = $pieces[0];
        if ((preg_match(self::VALID_VERSION, $version) === 0) || ($version !== self::SUPPORTED_VERSION)) {
            throw new \InvalidArgumentException(
                sprintf('Only version 00 is supported, got %s', $version)
            );
        }

        $traceId = $pieces[1];
        if ((preg_match(self::VALID_TRACE, $traceId) === 0) || ($traceId === self::INVALID_TRACE)) {
            throw new \InvalidArgumentException(
                sprintf('TraceID must be exactly 16 bytes (32 chars) and at least one non-zero byte, got %s', $traceId)
            );
        }

        $spanId = $pieces[2];
        if ((preg_match(self::VALID_SPAN, $spanId) === 0) || ($spanId === self::INVALID_SPAN)) {
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
        $isSampled = ($convertedTraceFlags & self::SAMPLED_FLAG) === self::SAMPLED_FLAG;

        return SpanContext::restore($traceId, $spanId, $isSampled, true);
    }
}
