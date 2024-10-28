<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Propagation;

use OpenTelemetry\API\Baggage\Propagation\BaggagePropagator;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Context\Propagation\MultiTextMapPropagator;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Registry;

class PropagatorFactory
{
    use LogsMessagesTrait;

    public function create(): TextMapPropagatorInterface
    {
        $propagators = Configuration::getList(Variables::OTEL_PROPAGATORS);

        return match (count($propagators)) {
            0 => new NoopTextMapPropagator(),
            1 => $this->buildPropagator($propagators[0]),
            default => new MultiTextMapPropagator($this->buildPropagators($propagators)),
        };
    }

    /**
     * @return list<TextMapPropagatorInterface>
     */
    private function buildPropagators(array $names): array
    {
        $propagators = [];
        foreach ($names as $name) {
            $propagators[] = $this->buildPropagator($name);
        }

        return $propagators;
    }

    private function buildPropagator(string $name): TextMapPropagatorInterface
    {
        switch ($name) {
            case KnownValues::VALUE_BAGGAGE:
                return BaggagePropagator::getInstance();
            case KnownValues::VALUE_TRACECONTEXT:
                return TraceContextPropagator::getInstance();
            case KnownValues::VALUE_NOOP:
            case KnownValues::VALUE_NONE:
                return NoopTextMapPropagator::getInstance();
            default:
                try {
                    return Registry::textMapPropagator($name);
                } catch (\RuntimeException $e) {
                    self::logWarning($e->getMessage());
                }
        }

        return NoopTextMapPropagator::getInstance();
    }
}
