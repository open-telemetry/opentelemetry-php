<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler;

use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Trace\SamplerInterface;

class ParentBasedTraceIdRatioSamplerFactory implements SamplerFactoryInterface
{
    #[\Override]
    public function create(): SamplerInterface
    {
        $ratio = Configuration::getRatio(Variables::OTEL_TRACES_SAMPLER_ARG);

        return new ParentBased(new TraceIdRatioBasedSampler($ratio));
    }
}
