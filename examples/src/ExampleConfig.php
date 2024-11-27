<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Instrumentation\AutoInstrumentation\InstrumentationConfiguration;

final class ExampleConfig implements InstrumentationConfiguration
{

    public function __construct(
        public readonly string $spanName,
        public readonly bool $enabled = true,
    ) {
    }

    public static function default(): self
    {
        return new self('example');
    }
}
