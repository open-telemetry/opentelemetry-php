<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\AutoInstrumentation;

final class ConfigurationRegistry
{

    private array $configurations = [];
    private array $general = [];

    public function add(InstrumentationConfiguration $configuration): self
    {
        $this->configurations[$configuration::class] = $configuration;

        return $this;
    }

    public function addGeneral(GeneralInstrumentationConfiguration $configuration): self
    {
        $this->general[$configuration::class] = $configuration;

        return $this;
    }

    /**
     * @template C of InstrumentationConfiguration
     * @param class-string<C> $id
     * @return C|null
     */
    public function get(string $id): ?InstrumentationConfiguration
    {
        return $this->configurations[$id] ?? null;
    }

    /**
     * @template C of GeneralInstrumentationConfiguration
     * @param class-string<C> $id
     * @return C|null
     */
    public function getGeneral(string $id): ?GeneralInstrumentationConfiguration
    {
        return $this->general[$id] ?? null;
    }
}
