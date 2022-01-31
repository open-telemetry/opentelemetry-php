<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;

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
                return new AlwaysOnSampler();
            case 'always_off':
                return new AlwaysOffSampler();
            case 'traceidratio':
                return new TraceIdRatioBasedSampler((float) $arg);
            case 'parentbased_always_on':
                return new ParentBased(new AlwaysOnSampler());
            case 'parentbased_always_off':
                return new ParentBased(new AlwaysOffSampler());
            case 'parentbased_traceidratio':
                return new ParentBased(new TraceIdRatioBasedSampler((float) $arg));
            default:
                throw new InvalidArgumentException('Unknown sampler: ' . $name);
        }
    }
}
