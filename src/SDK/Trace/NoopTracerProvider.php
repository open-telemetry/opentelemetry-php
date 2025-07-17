<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\API;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Configurator;

class NoopTracerProvider extends API\Trace\NoopTracerProvider implements TracerProviderInterface
{
    #[\Override]
    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }

    #[\Override]
    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }

    #[\Override]
    public function updateConfigurator(Configurator $configurator): void
    {
    }
}
