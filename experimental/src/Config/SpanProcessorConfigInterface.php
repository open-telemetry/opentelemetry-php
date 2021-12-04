<?php

declare(strict_types=1);

namespace OpenTelemetry\Experimental\Config;

interface SpanProcessorConfigInterface
{
    public static function provides(string $exporterName): bool;
}
