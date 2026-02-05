<?php

declare(strict_types=1);

namespace OpenTelemetry\Context\Propagation;

use OpenTelemetry\Context\ContextInterface;

/**
 * @experimental
 */
final class MultiResponsePropagator implements ResponsePropagatorInterface
{
    /**
     * @no-named-arguments
     *
     * @param list<ResponsePropagatorInterface> $responsePropagators
     */
    public function __construct(
        private readonly array $responsePropagators,
    ) {
    }

    #[\Override]
    public function inject(&$carrier, ?PropagationSetterInterface $setter = null, ?ContextInterface $context = null): void
    {
        foreach ($this->responsePropagators as $responsePropagator) {
            $responsePropagator->inject($carrier, $setter, $context);
        }
    }
}
