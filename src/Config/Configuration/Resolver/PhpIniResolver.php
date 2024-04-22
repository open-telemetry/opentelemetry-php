<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\Configuration\Resolver;

use OpenTelemetry\Config\Configuration\Accessor\PhpIniAccessor;
use OpenTelemetry\Config\Configuration\Configuration;

/**
 * @internal
 * @psalm-suppress TypeDoesNotContainType
 */
class PhpIniResolver implements ResolverInterface
{
    public function __construct(private readonly PhpIniAccessor $accessor = new PhpIniAccessor())
    {
    }

    public function retrieveValue(string $variableName): string|bool
    {
        $value = $this->accessor->get($variableName) ?: '';
        if (is_array($value)) {
            return implode(',', $value);
        }

        return $value;
    }

    public function hasVariable(string $variableName): bool
    {
        $value = $this->accessor->get($variableName);
        if ($value === []) {
            return false;
        }

        return $value !== false && !Configuration::isEmpty($value);
    }
}
