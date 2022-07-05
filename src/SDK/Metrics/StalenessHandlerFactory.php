<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

interface StalenessHandlerFactory
{
    public function create(): StalenessHandler&ReferenceCounter;
}
