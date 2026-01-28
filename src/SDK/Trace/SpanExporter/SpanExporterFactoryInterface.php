<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanExporter;

use OpenTelemetry\SDK\Trace\SpanExporterInterface;

/**
 * TODO deprecated
 */
interface SpanExporterFactoryInterface
{
    public function create(): SpanExporterInterface;
}
