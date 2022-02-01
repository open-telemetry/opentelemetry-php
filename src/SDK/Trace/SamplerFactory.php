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
        if ($name === '') {
            throw new InvalidArgumentException(sprintf('Env Var %s not set', Env::OTEL_TRACES_SAMPLER));
        }
        $arg = $this->getStringFromEnvironment(Env::OTEL_TRACES_SAMPLER_ARG);
        if (strpos($name, self::TRACEIDRATIO_PREFIX) !== false) {
            if ($arg === '') {
                throw new InvalidArgumentException(sprintf(
                    'Env Var %s required for ratio-based sampler: %s',
                    Env::OTEL_TRACES_SAMPLER_ARG,
                    $name
                ));
            }
            if (!is_numeric($arg)) {
                throw new InvalidArgumentException(sprintf('Env Var %s  value is not numeric', Env::OTEL_TRACES_SAMPLER_ARG));
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
                throw new InvalidArgumentException(sprintf('Unknown sampler: %s', $name));
        }
    }
}
