<?php declare(strict_types=1);
namespace OpenTelemetry\API\Instrumentation\SpanSuppression\NoopSuppressionStrategy;

use OpenTelemetry\API\Instrumentation\SpanSuppression\SpanSuppressionStrategy;
use OpenTelemetry\API\Instrumentation\SpanSuppression\SpanSuppressor;

final class NoopSuppressionStrategy implements SpanSuppressionStrategy {

    public function getSuppressor(string $name, ?string $version, ?string $schemaUrl): SpanSuppressor {
        return new NoopSuppressor();
    }
}
