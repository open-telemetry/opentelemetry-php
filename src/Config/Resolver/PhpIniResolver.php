<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\Resolver;

use OpenTelemetry\Config\Accessor\PhpIniAccessor;
use OpenTelemetry\Config\Configuration;

/**
 * @internal
 * @psalm-suppress TypeDoesNotContainType
 */
class PhpIniResolver implements ResolverInterface
{
    private PhpIniAccessor $accessor;

    public function __construct(?PhpIniAccessor $accessor = null)
    {
        $this->accessor = $accessor ?? new PhpIniAccessor();
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
