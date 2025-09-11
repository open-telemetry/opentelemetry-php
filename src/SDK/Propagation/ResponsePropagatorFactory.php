<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Propagation;

use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\Context\Propagation\MultiResponsePropagator;
use OpenTelemetry\Context\Propagation\NoopResponsePropagator;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Registry;

/**
 * @experimental
 */
class ResponsePropagatorFactory
{
    use LogsMessagesTrait;

    public function create(): ResponsePropagatorInterface
    {
        $responsePropagators = Configuration::getList(Variables::OTEL_EXPERIMENTAL_RESPONSE_PROPAGATORS);

        return match (count($responsePropagators)) {
            0 => new NoopResponsePropagator(),
            1 => $this->buildResponsePropagator($responsePropagators[0]),
            default => new MultiResponsePropagator($this->buildResponsePropagators($responsePropagators)),
        };
    }

    /**
     * @return list<ResponsePropagatorInterface>
     */
    private function buildResponsePropagators(array $names): array
    {
        $responsePropagators = [];
        foreach ($names as $name) {
            $responsePropagators[] = $this->buildResponsePropagator($name);
        }

        return $responsePropagators;
    }

    private function buildResponsePropagator(string $name): ResponsePropagatorInterface
    {
        try {
            return Registry::responsePropagator($name);
        } catch (\RuntimeException $e) {
            self::logWarning($e->getMessage());
        }

        return NoopResponsePropagator::getInstance();
    }
}
