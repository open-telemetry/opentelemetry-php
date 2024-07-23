<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\InstrumentationScope\Predicate;

use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate;

/**
 * Predicate which performs a match on {@link InstrumentationScope} name.
 * The name may use wildcards: * and ?
 */
class Name implements Predicate
{
    private readonly string $pattern;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $name)
    {
        $this->pattern = sprintf('/^%s$/', strtr(preg_quote($name, '/'), ['\\?' => '.', '\\*' => '.*']));
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function matches(InstrumentationScopeInterface $scope): bool
    {
        return (bool) preg_match($this->pattern, $scope->getName());
    }
}
