<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanSuppression;

/**
 * @experimental
 */
interface SpanSuppressionStrategy
{
    public function getSuppressor(string $name, ?string $version, ?string $schemaUrl): SpanSuppressor;
}
