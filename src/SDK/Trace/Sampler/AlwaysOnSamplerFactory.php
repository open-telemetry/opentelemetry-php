<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler;

use OpenTelemetry\SDK\Trace\SamplerInterface;

class AlwaysOnSamplerFactory implements SamplerFactoryInterface
{
    #[\Override]
    public function create(): SamplerInterface
    {
        return new AlwaysOnSampler();
    }
}
