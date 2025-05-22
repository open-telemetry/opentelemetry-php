<?php declare(strict_types=1);
namespace OpenTelemetry\API\Instrumentation\SpanSuppression;

interface SpanSuppressionStrategy {

    public function getSuppressor(string $name, ?string $version, ?string $schemaUrl): SpanSuppressor;
}
