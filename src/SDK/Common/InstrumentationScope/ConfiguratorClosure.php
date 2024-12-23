<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\InstrumentationScope;

use Closure;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;

/**
 * @internal
 */
final readonly class ConfiguratorClosure
{

    public function __construct(
        public Closure $closure,
        private ?string $name,
        private ?string $version,
        private ?string $schemaUrl,
    ) {
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function matches(InstrumentationScopeInterface $instrumentationScope): bool
    {
        return ($this->name === null || preg_match($this->name, $instrumentationScope->getName()))
            && ($this->version === null || $this->version === $instrumentationScope->getVersion())
            && ($this->schemaUrl === null || $this->schemaUrl === $instrumentationScope->getSchemaUrl());
    }
}
