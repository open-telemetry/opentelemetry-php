<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\StalenessHandler;

use OpenTelemetry\SDK\Metrics\StalenessHandlerFactoryInterface;
use OpenTelemetry\SDK\Metrics\StalenessHandlerInterface;

final class ImmediateStalenessHandlerFactory implements StalenessHandlerFactoryInterface
{
    public function create(): StalenessHandlerInterface
    {
        return new ImmediateStalenessHandler();
    }
}
