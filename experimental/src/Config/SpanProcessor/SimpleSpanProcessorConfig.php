<?php

declare(strict_types=1);

namespace OpenTelemetry\Experimental\Config\SpanProcessor;

use OpenTelemetry\Experimental\Config\ConfigInterface;
use OpenTelemetry\Experimental\Config\SpanProcessorConfigInterface;

class SimpleSpanProcessorConfig implements ConfigInterface, SpanProcessorConfigInterface
{
    public function __construct(array $userConfig, array $environmentConfig)
    {
    }

    public static function provides(string $exporterName): bool
    {
        return $exporterName === 'simple';
    }
}
