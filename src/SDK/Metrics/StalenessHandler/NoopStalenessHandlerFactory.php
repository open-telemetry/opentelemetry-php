<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\StalenessHandler;

use OpenTelemetry\SDK\Metrics\ReferenceCounterInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandlerFactoryInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandlerInterface;

final class NoopStalenessHandlerFactory implements StalenessHandlerFactoryInterface
{
    public function create(): ReferenceCounterInterface&StalenessHandlerInterface
    {
        static $instance;

        return $instance ??= new NoopStalenessHandler();
    }
}
