<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Instrumentation;

/**
 * Represents the instrumentation library information associated with the Tracer or Meter
 */
final class InstrumentationLibrary implements InstrumentationLibraryInterface
{
    private static ?self $empty = null;

    private string $name;
    private ?string $version;
    private ?string $schemaUrl;

    public function __construct(string $name, ?string $version = null, ?string $schemaUrl = null)
    {
        $this->name = $name;
        $this->version = $version;
        $this->schemaUrl = $schemaUrl;
    }

    /**
     * @internal
     * @psalm-internal OpenTelemetry
     */
    public static function getEmpty(): InstrumentationLibrary
    {
        return self::$empty ?? self::$empty = new self('', null, null);
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
