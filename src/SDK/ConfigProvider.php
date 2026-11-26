<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use OpenTelemetry\API\Configuration\ConfigProperties;
use OpenTelemetry\API\Configuration\ConfigProviderInterface;

/**
 * @experimental
 */
final class ConfigProvider implements ConfigProviderInterface
{
    public function __construct(
        private readonly ConfigProperties $instrumentationConfig,
    ) {
    }

    #[\Override]
    public function getInstrumentationConfig(): ConfigProperties
    {
        return $this->instrumentationConfig;
    }
}
