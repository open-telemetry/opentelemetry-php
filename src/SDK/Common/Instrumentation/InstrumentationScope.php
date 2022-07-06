<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Instrumentation;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

/**
 * Represents the instrumentation scope information associated with the Tracer or Meter
 */
final class InstrumentationScope implements InstrumentationScopeInterface
{
    private string $name;
    private ?string $version;
    private ?string $schemaUrl;
    private AttributesInterface $attributes;

    public function __construct(string $name, ?string $version, ?string $schemaUrl, AttributesInterface $attributes)
    {
        $this->name = $name;
        $this->version = $version;
        $this->schemaUrl = $schemaUrl;
        $this->attributes = $attributes;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function getSchemaUrl(): ?string
    {
        return $this->schemaUrl;
    }

    public function getAttributes(): AttributesInterface
    {
        return $this->attributes;
    }
}
