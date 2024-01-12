<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration\Resolver;

use OpenTelemetry\SDK\Common\Configuration\Configuration;

/**
 * @internal
 */
class CompositeResolver
{
    // @var array<ResolverInterface>
    private array $resolvers = [];

    public static function instance(): self
    {
        static $instance;
        $instance ??= new self([
            new EnvironmentResolver(),
            new PhpIniResolver(),
        ]);

        return $instance;
    }

    public function __construct($resolvers)
    {
        foreach ($resolvers as $resolver) {
            $this->addResolver($resolver);
        }
    }

    public function addResolver(ResolverInterface $resolver): void
    {
        $this->resolvers[] = $resolver;
    }

    public function getResolvers(): array
    {
        return $this->resolvers;
    }

    public function resolve(string $variableName, $default = '')
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->hasVariable($variableName)) {
                return $resolver->retrieveValue($variableName);
            }
        }

        return Configuration::isEmpty($default)
            ? Configuration::getDefault($variableName)
            : $default;
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
