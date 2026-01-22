<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler;

use OpenTelemetry\SDK\Trace\SamplerInterface;

class ParentBasedAlwaysOffSamplerFactory implements SamplerFactoryInterface
{
    #[\Override]
    public function create(): SamplerInterface
    {
        return new ParentBased(new AlwaysOffSampler());
    }
}
