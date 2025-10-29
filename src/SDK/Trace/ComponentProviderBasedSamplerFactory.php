<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use InvalidArgumentException;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SamplerAlwaysOff;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SamplerAlwaysOn;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SamplerParentBased;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SamplerTraceIdRatioBased;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\KnownValues as Values;
use OpenTelemetry\SDK\Common\Configuration\Variables as Env;

/**
 * ComponentProvider-based Sampler factory that reads configuration from environment variables
 * and uses the modern ComponentProvider system for component creation.
 */
class ComponentProviderBasedSamplerFactory
{
    private const TRACEIDRATIO_PREFIX = 'traceidratio';

    private Context $context;

    public function __construct()
    {
        $this->context = new Context();
    }

    public function create(): SamplerInterface
    {
        $name = Configuration::getString(Env::OTEL_TRACES_SAMPLER);

        if (str_contains($name, self::TRACEIDRATIO_PREFIX)) {
            $ratio = Configuration::getRatio(Env::OTEL_TRACES_SAMPLER_ARG);

            switch ($name) {
                case Values::VALUE_TRACE_ID_RATIO:
                    return $this->createTraceIdRatioSampler($ratio);
                case Values::VALUE_PARENT_BASED_TRACE_ID_RATIO:
                    return $this->createParentBasedTraceIdRatioSampler($ratio);
            }
        }

        return match ($name) {
            Values::VALUE_ALWAYS_ON => $this->createAlwaysOnSampler(),
            Values::VALUE_ALWAYS_OFF => $this->createAlwaysOffSampler(),
            Values::VALUE_PARENT_BASED_ALWAYS_ON => $this->createParentBasedAlwaysOnSampler(),
            Values::VALUE_PARENT_BASED_ALWAYS_OFF => $this->createParentBasedAlwaysOffSampler(),
            default => throw new InvalidArgumentException(sprintf('Unknown sampler: %s', $name)),
        };
    }

    private function createAlwaysOnSampler(): SamplerInterface
    {
        $provider = new SamplerAlwaysOn();
        return $provider->createPlugin([], $this->context);
    }

    private function createAlwaysOffSampler(): SamplerInterface
    {
        $provider = new SamplerAlwaysOff();
        return $provider->createPlugin([], $this->context);
    }

    private function createTraceIdRatioSampler(float $ratio): SamplerInterface
    {
        $provider = new SamplerTraceIdRatioBased();
        $config = ['ratio' => $ratio];
        return $provider->createPlugin($config, $this->context);
    }

    private function createParentBasedAlwaysOnSampler(): SamplerInterface
    {
        $provider = new SamplerParentBased();
        $config = [
            'root' => new SamplerComponentPlugin($this->createAlwaysOnSampler()),
        ];
        return $provider->createPlugin($config, $this->context);
    }

    private function createParentBasedAlwaysOffSampler(): SamplerInterface
    {
        $provider = new SamplerParentBased();
        $config = [
            'root' => new SamplerComponentPlugin($this->createAlwaysOffSampler()),
        ];
        return $provider->createPlugin($config, $this->context);
    }

    private function createParentBasedTraceIdRatioSampler(float $ratio): SamplerInterface
    {
        $provider = new SamplerParentBased();
        $config = [
            'root' => new SamplerComponentPlugin($this->createTraceIdRatioSampler($ratio)),
        ];
        return $provider->createPlugin($config, $this->context);
    }
}

/**
 * Simple ComponentPlugin wrapper for existing Sampler instances
 */
class SamplerComponentPlugin
{
    public function __construct(private readonly SamplerInterface $sampler) {}

    public function create(Context $context): SamplerInterface
    {
        return $this->sampler;
    }
}
