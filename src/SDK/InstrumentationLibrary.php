<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

/**
 * Represents the instrumentation library information associated with the Tracer or Meter
 */
class InstrumentationLibrary
{
    private static ?self $empty = null;

    /**
     * @internal
     * @psalm-internal OpenTelemetry
     */
    public static function getEmpty(): InstrumentationLibrary
    {
        if (null === self::$empty) {
            self::$empty = new self('', null, null);
        }

        return self::$empty;
    }

    private string $name;
    private ?string $version;
    private ?string $schemaUrl;

    public function __construct(string $name, ?string $version = null, ?string $schemaUrl = null)
    {
        $this->name = $name;
        $this->version = $version;
        $this->schemaUrl = $schemaUrl;
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
}
