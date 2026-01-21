<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Instrumentation;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

/**
 * Represents the instrumentation scope information associated with the Tracer or Meter
 */
final readonly class InstrumentationScope implements InstrumentationScopeInterface
{
    public function __construct(
        private string $name,
        private ?string $version,
        private ?string $schemaUrl,
        private AttributesInterface $attributes,
    ) {
    }

    #[\Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[\Override]
    public function getVersion(): ?string
    {
        return $this->version;
    }

    #[\Override]
    public function getSchemaUrl(): ?string
    {
        return $this->schemaUrl;
    }

    #[\Override]
    public function getAttributes(): AttributesInterface
    {
        return $this->attributes;
    }
}
