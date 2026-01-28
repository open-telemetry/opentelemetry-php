<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables as Env;
use OpenTelemetry\SDK\Registry;
use RuntimeException;

class SamplerFactory
{
    public function create(): SamplerInterface
    {
        $name = Configuration::getString(Env::OTEL_TRACES_SAMPLER);

        try {
            $factory = Registry::samplerFactory($name);

            return $factory->create();
        } catch (RuntimeException) {
            throw new InvalidArgumentException(sprintf('Unknown sampler: %s', $name));
        }
    }
}
