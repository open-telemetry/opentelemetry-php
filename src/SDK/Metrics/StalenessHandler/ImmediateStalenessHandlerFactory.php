<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\StalenessHandler;

use OpenTelemetry\SDK\Metrics\StalenessHandlerFactoryInterface;

final class ImmediateStalenessHandlerFactory implements StalenessHandlerFactoryInterface
{
    public function create()
    {
        return new ImmediateStalenessHandler();
    }
}
