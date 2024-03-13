<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Instrumentation;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

/**
 * Represents the instrumentation scope information associated with the Tracer or Meter
 */
final class InstrumentationScope implements InstrumentationScopeInterface
{
    public function __construct(
        private readonly string $name,
        private readonly ?string $version,
        private readonly ?string $schemaUrl,
        private readonly AttributesInterface $attributes,
    ) {
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
