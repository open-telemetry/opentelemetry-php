<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

interface StalenessHandlerFactory
{
    /**
     * @return StalenessHandler&ReferenceCounter
     */
    public function create();
}
