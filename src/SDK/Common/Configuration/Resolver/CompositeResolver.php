<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration\Resolver;

use OpenTelemetry\SDK\Common\Configuration\Resolver;

/**
 * @interal
 */
class CompositeResolver extends Resolver
{
    private array $resolvers = [];

    public static function instance(): self
    {
        static $instance;
        $instance ??= new self([
            new IniResolver(),
            new EnvironmentResolver(),
        ]);

        return $instance;
    }

    public function __construct($resolvers)
    {
        foreach ($resolvers as $resolver) {
            $this->addResolver($resolver);
        }
    }

    private function addResolver(Resolver $resolver): void
    {
        $this->resolvers[] = $resolver;
    }

    public function retrieveValue(string $variableName, ?string $default = ''): ?string
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->hasVariable($variableName)) {
                return $resolver->retrieveValue($variableName);
            }
        }

        return $default;
    }

    public function hasVariable(string $variableName): bool
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->hasVariable($variableName)) {
                return true;
            }
        }

        return false;
    }
}
