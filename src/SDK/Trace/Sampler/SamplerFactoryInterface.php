<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Sampler;

use OpenTelemetry\SDK\Trace\SamplerInterface;

interface SamplerFactoryInterface
{
    public function create(): SamplerInterface;
}
