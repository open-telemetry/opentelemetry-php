<?php

declare(strict_types=1);

namespace OpenTelemetry\Extension\Propagator\B3;

use OpenTelemetry\SDK\RegistrarInterface;
use OpenTelemetry\SDK\Registry;

/**
 * @internal
 */
final class Registrar implements RegistrarInterface
{
    #[\Override]
    public function register(): void
    {
        Registry::registerTextMapPropagator('b3', B3Propagator::getB3SingleHeaderInstance());
        Registry::registerTextMapPropagator('b3multi', B3Propagator::getB3MultiHeaderInstance());
    }
}
