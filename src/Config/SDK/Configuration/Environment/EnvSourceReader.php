<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Environment;

use function is_string;
use function trim;

final readonly class EnvSourceReader implements EnvReader
{

    /**
     * @param iterable<EnvSource> $envSources
     */
    public function __construct(
        private iterable $envSources,
    ) {
    }

    public function read(string $name): ?string
    {
        foreach ($this->envSources as $envSource) {
            if (is_string($value = $envSource->readRaw($name)) && ($value = trim($value, " \t")) !== '') {
                return $value;
            }
        }

        return null;
    }
}
