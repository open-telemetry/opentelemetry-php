<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk;

/**
 * Represents the instrumentation library information associated with the Tracer or Meter
 */
class InstrumentationLibrary
{
    private $name;

    private $version;

    public function __construct(string $name, ?string $version = null)
    {
        $this->name = $name;
        $this->version = $version;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }
}
