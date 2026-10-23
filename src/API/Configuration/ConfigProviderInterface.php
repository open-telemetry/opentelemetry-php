<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Configuration;

/**
 * @experimental
 *
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/configuration/api.md#configprovider
 */
interface ConfigProviderInterface
{
    public function getInstrumentationConfig(Context $context = new Context()): ConfigProperties;
}
