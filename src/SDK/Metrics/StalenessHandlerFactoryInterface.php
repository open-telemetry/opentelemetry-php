<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

interface StalenessHandlerFactoryInterface
{
    /**
     * @return StalenessHandlerInterface&ReferenceCounterInterface
     */
    public function create(): StalenessHandlerInterface;
}
