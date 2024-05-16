<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use Closure;

class LateBindingTracerProvider implements TracerProviderInterface
{
    private ?TracerProviderInterface $tracerProvider = null;

    /** @param Closure(): TracerProviderInterface $factory */
    public function __construct(
        private readonly Closure $factory,
    ) {
    }

    public function getTracer(string $name, ?string $version = null, ?string $schemaUrl = null, iterable $attributes = []): TracerInterface
    {
        return $this->tracerProvider?->getTracer($name, $version, $schemaUrl, $attributes)
            ?? new LateBindingTracer(fn (): TracerInterface => ($this->tracerProvider ??= ($this->factory)())->getTracer($name, $version, $schemaUrl, $attributes));
    }
}
