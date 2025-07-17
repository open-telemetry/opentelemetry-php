<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use Closure;

class LateBindingTracer implements TracerInterface
{
    private ?TracerInterface $tracer = null;

    /** @param Closure(): TracerInterface $factory */
    public function __construct(
        private readonly Closure $factory,
    ) {
    }

    #[\Override]
    public function spanBuilder(string $spanName): SpanBuilderInterface
    {
        return ($this->tracer ??= ($this->factory)())->spanBuilder($spanName);
    }

    #[\Override]
    public function isEnabled(): bool
    {
        return true;
    }
}
