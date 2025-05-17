<?php declare(strict_types=1);
namespace OpenTelemetry\API\Instrumentation\SpanSuppression\SpanKindSuppressionStrategy;

use OpenTelemetry\API\Instrumentation\SpanSuppression\SpanSuppressionStrategy;
use OpenTelemetry\API\Instrumentation\SpanSuppression\SpanSuppressor;

final class SpanKindSuppressionStrategy implements SpanSuppressionStrategy {

    public function getSuppressor(string $name, ?string $version, ?string $schemaUrl): SpanSuppressor {
        return new SpanKindSuppressor();
    }
}
