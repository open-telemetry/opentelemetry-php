<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\StalenessHandler;

use OpenTelemetry\SDK\Metrics\StalenessHandlerFactory;

final class NoopStalenessHandlerFactory implements StalenessHandlerFactory
{
    public function create()
    {
        static $instance;

        return $instance ??= new NoopStalenessHandler();
    }
}
