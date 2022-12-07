<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Propagation;

use OpenTelemetry\Context\Propagation\MultiTextMapPropagator;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\FactoryRegistry;

class PropagatorFactory
{
    use LogsMessagesTrait;

    public function create(): TextMapPropagatorInterface
    {
        $propagators = Configuration::getList(Variables::OTEL_PROPAGATORS);
        switch (count($propagators)) {
            case 0:
                return new NoopTextMapPropagator();
            case 1:
                return $this->buildPropagator($propagators[0]);
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
            $propagators[] = $this->buildPropagator($name);
        }

        return $propagators;
    }

    private function buildPropagator(string $name): TextMapPropagatorInterface
    {
        try {
            return FactoryRegistry::textMapPropagator($name);
        } catch (\RuntimeException $e) {
            self::logWarning($e->getMessage());
        }

        return NoopTextMapPropagator::getInstance();
    }
}
