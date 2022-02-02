<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Common\Environment\Variables as Env;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;

class SamplerFactory
{
    use EnvironmentVariablesTrait;

    private const TRACEIDRATIO_PREFIX = 'traceidratio';

    public function fromEnvironment(): SamplerInterface
    {
        $name = $this->getStringFromEnvironment(Env::OTEL_TRACES_SAMPLER);

        if (strpos($name, self::TRACEIDRATIO_PREFIX) !== false) {
            $arg = $this->getRatioFromEnvironment(Env::OTEL_TRACES_SAMPLER_ARG);

            switch ($name) {
                case 'traceidratio':
                    return new TraceIdRatioBasedSampler($arg);
                case 'parentbased_traceidratio':
                    return new ParentBased(new TraceIdRatioBasedSampler($arg));
            }
        }

        switch ($name) {
            case 'always_on':
                return new AlwaysOnSampler();
            case 'always_off':
                return new AlwaysOffSampler();
            case 'parentbased_always_on':
                return new ParentBased(new AlwaysOnSampler());
            case 'parentbased_always_off':
                return new ParentBased(new AlwaysOffSampler());
            default:
                throw new InvalidArgumentException(sprintf('Unknown sampler: %s', $name));
        }
    }
}
