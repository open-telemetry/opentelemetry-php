<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanProcessor;

use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Metrics\HistogramInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Trace\ReadableSpanInterface;
use OpenTelemetry\SDK\Trace\ReadWriteSpanInterface;
use OpenTelemetry\SDK\Trace\SpanProcessorInterface;
use OpenTelemetry\SemConv\Metrics\HttpMetrics;
use OpenTelemetry\SemConv\TraceAttributes;

/**
 * A SpanProcessor that records HTTP server request metrics.
 *
 * @see https://opentelemetry.io/docs/specs/semconv/http/http-metrics
 */
class HttpMetricsSpanProcessor implements SpanProcessorInterface
{
    private readonly HistogramInterface $serverRequestDuration;

    public function __construct(MeterProviderInterface $meterProvider)
    {
        $this->serverRequestDuration = $meterProvider
            ->getMeter('io.opentelemetry.php.span_processor.http_metrics')
            ->createHistogram(
                name: HttpMetrics::HTTP_SERVER_REQUEST_DURATION,
                unit: 's',
                description: 'Duration of HTTP server requests',
                advisory: [
                    'ExplicitBucketBoundaries' => [0.005, 0.01, 0.025, 0.05, 0.075, 0.1, 0.25, 0.5, 0.75, 1, 2.5, 5, 7.5, 10],
                ],
            );
    }

    /**
     * @inheritDoc
     */
    public function onStart(ReadWriteSpanInterface $span, ContextInterface $parentContext): void
    {
        // do nothing
    }

    /**
     * @inheritDoc
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function onEnd(ReadableSpanInterface $span): void
    {
        if (
            $span->getKind() !== SpanKind::KIND_SERVER ||
            $span->getAttribute(TraceAttributes::HTTP_REQUEST_METHOD) === null ||
            !$this->serverRequestDuration->isEnabled()
        ) {
            return;
        }

        $required = [
            TraceAttributes::HTTP_REQUEST_METHOD,
            TraceAttributes::HTTP_RESPONSE_STATUS_CODE,
            TraceAttributes::HTTP_ROUTE,
            TraceAttributes::URL_SCHEME,
            TraceAttributes::EXCEPTION_TYPE,
            TraceAttributes::HTTP_RESPONSE_BODY_SIZE,
            TraceAttributes::NETWORK_PROTOCOL_VERSION,
            TraceAttributes::HTTP_RESPONSE_BODY_SIZE,
            TraceAttributes::HTTP_ROUTE,
        ];

        $attributes = [];
        foreach ($required as $key) {
            $attributes[$key] = $span->getAttribute($key);
        }
        $duration = $span->getDuration() / ClockInterface::NANOS_PER_SECOND;
        $this->serverRequestDuration->record((float) $duration, $attributes);
    }

    /**
     * @inheritDoc
     */
    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }

    public function onEnding(ReadWriteSpanInterface $span): void
    {
        // do nothing
    }
}
