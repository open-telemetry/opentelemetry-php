<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Trace;

class InstrumentationLibrary
{
    private $name;

    private $version;

    public function __construct(string $name, ?string $version = '')
    {
        $this->name = $name;
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }
}
