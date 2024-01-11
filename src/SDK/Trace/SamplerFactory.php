<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\KnownValues as Values;
use OpenTelemetry\SDK\Common\Configuration\Variables as Env;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOffSampler;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\AttributeBasedSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\Sampler\TraceIdRatioBasedSampler;

class SamplerFactory
{
    private const TRACEIDRATIO_PREFIX = 'traceidratio';

    public function create(): SamplerInterface
    {
        $name = Configuration::getString(Env::OTEL_TRACES_SAMPLER);
        if (strpos($name, ',') !== false) {
            $parts = explode(',', $name);
            $arg = Configuration::has(Env::OTEL_TRACES_SAMPLER_ARG) ? Configuration::getString(Env::OTEL_TRACES_SAMPLER_ARG) : '';

            return $this->buildCompositeSampler($parts, $arg);
        }

        if (strpos($name, self::TRACEIDRATIO_PREFIX) !== false) {
            $arg = Configuration::getRatio(Env::OTEL_TRACES_SAMPLER_ARG);

            switch ($name) {
                case Values::VALUE_TRACE_ID_RATIO:
                    return new TraceIdRatioBasedSampler($arg);
                case Values::VALUE_PARENT_BASED_TRACE_ID_RATIO:
                    return new ParentBased(new TraceIdRatioBasedSampler($arg));
            }
        }

        switch ($name) {
            case Values::VALUE_ALWAYS_ON:
                return new AlwaysOnSampler();
            case Values::VALUE_ALWAYS_OFF:
                return new AlwaysOffSampler();
            case Values::VALUE_PARENT_BASED_ALWAYS_ON:
                return new ParentBased(new AlwaysOnSampler());
            case Values::VALUE_PARENT_BASED_ALWAYS_OFF:
                return new ParentBased(new AlwaysOffSampler());
            default:
                throw new InvalidArgumentException(sprintf('Unknown sampler: %s', $name));
        }
    }

    private function buildCompositeSampler(array $names, string $args): SamplerInterface
    {
        $sampler = null;
        foreach (array_reverse($names) as $name) {
            $sampler = $this->buildSampler($name, $args, $sampler);
        }
        assert($sampler !== null);

        return $sampler;
    }

    private function buildSampler(string $name, string $args, ?SamplerInterface $next): SamplerInterface
    {
        switch ($name) {
            case Values::VALUE_ALWAYS_ON:
                return new AlwaysOnSampler();
            case Values::VALUE_ALWAYS_OFF:
                return new AlwaysOffSampler();
            case Values::VALUE_PARENT_BASED:
                assert($next !== null);

                return new ParentBased($next);
            case Values::VALUE_TRACE_ID_RATIO:
                if (strpos($args, '=') !== false) {
                    $probability = $this->splitArgs($args)[$name]['probability'] ?? null;
                    if ($probability === null) {
                        throw new InvalidArgumentException(sprintf('%s.probability=(numeric) not found in %s', $name, Env::OTEL_TRACES_SAMPLER_ARG));
                    }
                } else {
                    //per specification, args may hold a single value
                    $probability = $args;
                }

                return new TraceIdRatioBasedSampler((float) $probability);
            case Values::VALUE_ATTRIBUTE:
                $split = $this->splitArgs($args)[$name];
                assert($next !== null);

                return new AttributeBasedSampler($next, $split['mode'], $split['name'], sprintf('/%s/', $split['pattern']));

            default:
                //@todo check a registry to support 3rd party samplers
                throw new InvalidArgumentException('Unknown sampler: ' . $name);
        }
    }

    /**
     * Split arguments from <name>.<key>=<value>,<name2>.<key2>=<value2> into an associative array
     */
    private function splitArgs(string $args): array
    {
        $return = [];
        $parts = explode(',', $args);
        foreach ($parts as $part) {
            $equals = strpos($part, '=');
            if ($equals === false) {
                throw new InvalidArgumentException('Error parsing sampler arguments');
            }
            [$name, $key] = explode('.', substr($part, 0, $equals));
            if (!array_key_exists($name, $return)) {
                $return[$name] = [];
            }
            $return[$name][$key] = substr($part, $equals + 1);
        }

        return $return;
    }
}
