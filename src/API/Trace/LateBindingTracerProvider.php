<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

use Closure;

/**
 * Late binding providers are designed to be used by Instrumentation, while we do not have control over when all components (propagators, etc)
 * which are registered through composer.autoload.files are actually loaded. It means that tracers etc are not fetched
 * from Globals until the last possible instant (ie, when they try to create a span, get an instrument, etc).
 * In the future, when everything uses SPI, this will be removed.
 */
class LateBindingTracerProvider implements TracerProviderInterface
{
    private ?TracerProviderInterface $tracerProvider = null;

    /** @param Closure(): TracerProviderInterface $factory */
    public function __construct(
        private readonly Closure $factory,
    ) {
    }

    #[\Override]
    public function getTracer(string $name, ?string $version = null, ?string $schemaUrl = null, iterable $attributes = []): TracerInterface
    {
        return $this->tracerProvider?->getTracer($name, $version, $schemaUrl, $attributes)
            ?? new LateBindingTracer(fn (): TracerInterface => ($this->tracerProvider ??= ($this->factory)())->getTracer($name, $version, $schemaUrl, $attributes));
    }
}
