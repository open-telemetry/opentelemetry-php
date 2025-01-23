<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanExporter;

use OpenTelemetry\SDK\Common\Services\SpiLoadableInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

interface SpanExporterFactoryInterface extends SpiLoadableInterface
{
    public function create(): SpanExporterInterface;
}
