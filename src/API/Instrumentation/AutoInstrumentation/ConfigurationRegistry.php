<?php declare(strict_types=1);
namespace OpenTelemetry\API\Instrumentation\AutoInstrumentation;

final class ConfigurationRegistry {

    private array $configurations = [];

    public function add(InstrumentationConfiguration $configuration): self {
        $this->configurations[$configuration::class] = $configuration;

        return $this;
    }

    /**
     * @template C of InstrumentationConfiguration
     * @param class-string<C> $id
     * @return C|null
     */
    public function get(string $id): ?InstrumentationConfiguration {
        return $this->configurations[$id] ?? null;
    }
}
