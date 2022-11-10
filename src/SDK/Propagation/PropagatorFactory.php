<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Propagation;

use OpenTelemetry\Context\Propagation\MultiTextMapPropagator;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Common\Configuration\Variables;

class PropagatorFactory
{
    use LogsMessagesTrait;

    private const KNOWN_PROPAGATORS = [
        KnownValues::VALUE_TRACECONTEXT => ['\OpenTelemetry\API\Trace\Propagation\TraceContextPropagator', 'getInstance'],
        KnownValues::VALUE_BAGGAGE => ['\OpenTelemetry\API\Baggage\Propagation\BaggagePropagator', 'getInstance'],
        KnownValues::VALUE_B3 => ['\OpenTelemetry\Extension\Propagator\B3\B3Propagator', 'getB3SingleHeaderInstance'],
        KnownValues::VALUE_B3_MULTI => ['\OpenTelemetry\Extension\Propagator\B3\B3Propagator', 'getB3MultiHeaderInstance'],
    ];

    public function create(): TextMapPropagatorInterface
    {
        $propagators = Configuration::getList(Variables::OTEL_PROPAGATORS);
        switch (count($propagators)) {
            case 0:
                return new NoopTextMapPropagator();
            case 1:
                return $this->buildPropagator($propagators[0]) ?? new NoopTextMapPropagator();
            default:
                return new MultiTextMapPropagator($this->buildPropagators($propagators));
        }
    }

    /**
     * @return array<TextMapPropagatorInterface>
     */
    private function buildPropagators(array $names): array
    {
        $propagators = [];
        foreach ($names as $name) {
            $propagator = $this->buildPropagator($name);
            if ($propagator !== null) {
                $propagators[] = $propagator;
            }
        }

        return $propagators;
    }

    private function buildPropagator(string $name): ?TextMapPropagatorInterface
    {
        switch ($name) {
            case KnownValues::VALUE_NONE:
                return null;
            case KnownValues::VALUE_XRAY:
            case KnownValues::VALUE_OTTRACE:
                self::logWarning('Unimplemented propagator: ' . $name);

                return null;
            default:
                if (!array_key_exists($name, self::KNOWN_PROPAGATORS)) {
                    self::logWarning('Unknown propagator: ' . $name);

                    return null;
                }
                $parts = self::KNOWN_PROPAGATORS[$name];

                try {
                    return call_user_func($parts);
                } catch (\Throwable $e) {
                    self::logError(sprintf('Unable to create %s propagator: %s', $name, $e->getMessage()));

                    return null;
                }

        }
    }
}
