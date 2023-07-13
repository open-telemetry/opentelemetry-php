<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Configuration\Resolver;

/**
 * @psalm-internal \OpenTelemetry\API\Configuration
 */
class CompositeResolver
{
    // @var array<ResolverInterface>
    private array $resolvers = [];

    public static function instance(): self
    {
        static $instance;
        $instance ??= new self([
            new PhpIniResolver(),
            new EnvironmentResolver(),
            new DotEnvResolver(),
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
