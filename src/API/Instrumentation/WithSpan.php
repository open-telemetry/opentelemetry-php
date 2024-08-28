<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation;

use Attribute;
use OpenTelemetry\API\Trace\SpanKind; //@phan-suppress-current-line PhanUnreferencedUseNormal

/**
 * Functions and methods with this attribute will be auto-instrumented
 * by the OpenTelemetry extension.
 */
#[Attribute(Attribute::TARGET_FUNCTION|Attribute::TARGET_METHOD)]
final class WithSpan
{
    /**
     * @param string|null $span_name Optional span name. Default: function name or class::method
     * @param int|null $span_kind Optional {@link SpanKind}. Default: {@link SpanKind::KIND_INTERNAL}
     * @param array $attributes Optional attributes to be added to the span.
     */
    public function __construct(
        public readonly ?string $span_name = null,
        public readonly ?int $span_kind = null,
        public readonly array $attributes = [],
    ) {
    }
}
