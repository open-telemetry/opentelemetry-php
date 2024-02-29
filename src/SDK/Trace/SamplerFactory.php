<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\KnownValues as Values;
use OpenTelemetry\SDK\Common\Configuration\Variables as Env;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;

class SamplerFactory
{
    private const TRACEIDRATIO_PREFIX = 'traceidratio';

    public function create(): SamplerInterface
    {
        $name = Configuration::getString(Env::OTEL_TRACES_SAMPLER);

        if (str_contains($name, self::TRACEIDRATIO_PREFIX)) {
            $arg = Configuration::getRatio(Env::OTEL_TRACES_SAMPLER_ARG);

            switch ($name) {
                case Values::VALUE_TRACE_ID_RATIO:
                    return new TraceIdRatioBasedSampler($arg);
                case Values::VALUE_PARENT_BASED_TRACE_ID_RATIO:
                    return new ParentBased(new TraceIdRatioBasedSampler($arg));
            }
        }

        return match ($name) {
            Values::VALUE_ALWAYS_ON => new AlwaysOnSampler(),
            Values::VALUE_ALWAYS_OFF => new AlwaysOffSampler(),
            Values::VALUE_PARENT_BASED_ALWAYS_ON => new ParentBased(new AlwaysOnSampler()),
            Values::VALUE_PARENT_BASED_ALWAYS_OFF => new ParentBased(new AlwaysOffSampler()),
            default => throw new InvalidArgumentException(sprintf('Unknown sampler: %s', $name)),
        };
    }
}
