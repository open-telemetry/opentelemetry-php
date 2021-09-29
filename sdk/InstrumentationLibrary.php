<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk;

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
            self::$empty = new self('', null);
        }

        return self::$empty;
    }

    private string $name;
    private ?string $version;

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
