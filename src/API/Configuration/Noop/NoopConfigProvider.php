<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Configuration\Noop;

use OpenTelemetry\API\Configuration\ConfigProperties;
use OpenTelemetry\API\Configuration\ConfigProviderInterface;
use OpenTelemetry\API\Configuration\Context;

/**
 * @experimental
 */
class NoopConfigProvider implements ConfigProviderInterface
{
    #[\Override]
    public function getInstrumentationConfig(Context $context = new Context()): ConfigProperties
    {
        return new NoopConfigProperties();
    }
}
