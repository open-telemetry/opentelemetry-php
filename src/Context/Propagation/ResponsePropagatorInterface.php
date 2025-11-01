<?php

declare(strict_types=1);

namespace OpenTelemetry\Context\Propagation;

use OpenTelemetry\Context\ContextInterface;

/**
 * @experimental
 */
interface ResponsePropagatorInterface
{
    /**
     * Injects specific values from the provided {@see ContextInterface} into the provided carrier
     * via an {@see PropagationSetterInterface}.
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.48.0/specification/context/api-propagators.md#textmap-inject
     * @experimental
     */
    public function inject(mixed &$carrier, ?PropagationSetterInterface $setter = null, ?ContextInterface $context = null): void;
}
