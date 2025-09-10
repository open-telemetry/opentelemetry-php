<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Propagation;

use Closure;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\Propagation\PropagationSetterInterface;
use OpenTelemetry\Context\Propagation\ResponsePropagatorInterface;

/**
 * @internal
 */
final class LateBindingResponsePropagator implements ResponsePropagatorInterface
{
    /**
     * @param ResponsePropagatorInterface|Closure(): ResponsePropagatorInterface $responsePropagator
     */
    public function __construct(
        private ResponsePropagatorInterface|Closure $responsePropagator,
    ) {
    }

    #[\Override]
    public function inject(mixed &$carrier, ?PropagationSetterInterface $setter = null, ?ContextInterface $context = null): void
    {
        if (!$this->responsePropagator instanceof ResponsePropagatorInterface) {
            $this->responsePropagator = ($this->responsePropagator)();
        }

        $this->responsePropagator->inject($carrier, $setter, $context);
    }
}
