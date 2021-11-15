<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use InvalidArgumentException;
use OpenTelemetry\SDK\EnvironmentVariablesTrait;

class SamplerFactory
{
    use EnvironmentVariablesTrait;

    public function fromEnvironment(): SamplerInterface
    {
        $name = $this->getStringFromEnvironment('OTEL_TRACES_SAMPLER', '');
        if (!$name) {
            throw new InvalidArgumentException('OTEL_TRACES_SAMPLER not set');
        }
        $arg = $this->getStringFromEnvironment('OTEL_TRACES_SAMPLER_ARG', '');
        if (false !== strpos($name, 'traceidratio')) {
            if (!$arg) {
                throw new InvalidArgumentException('OTEL_TRACES_SAMPLER_ARG required for ratio-based sampler: ' . $name);
            }
            if (!is_numeric($arg)) {
                throw new InvalidArgumentException('OTEL_TRACES_SAMPLER_ARG value is not numeric');
            }
        }
        switch ($name) {
            case 'always_on':
                return new Sampler\AlwaysOnSampler();
            case 'always_off':
                return new Sampler\AlwaysOffSampler();
            case 'traceidratio':
                return new Sampler\TraceIdRatioBasedSampler((float) $arg);
            case 'parentbased_always_on':
                return new Sampler\ParentBased(new Sampler\AlwaysOnSampler());
            case 'parentbased_always_off':
                return new Sampler\ParentBased(new Sampler\AlwaysOffSampler());
            case 'parentbased_traceidratio':
                return new Sampler\ParentBased(new Sampler\TraceIdRatioBasedSampler((float) $arg));
            default:
                throw new InvalidArgumentException('Unknown sampler: ' . $name);
        }
    }
}
