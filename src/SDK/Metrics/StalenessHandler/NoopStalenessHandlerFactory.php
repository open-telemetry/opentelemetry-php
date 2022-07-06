<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\StalenessHandler;

use OpenTelemetry\SDK\Metrics\StalenessHandlerFactoryInterface;

final class NoopStalenessHandlerFactory implements StalenessHandlerFactoryInterface
{
    public function create()
    {
        static $instance;

        return $instance ??= new NoopStalenessHandler();
    }
}
