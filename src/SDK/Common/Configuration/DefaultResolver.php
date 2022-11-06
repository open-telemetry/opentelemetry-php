<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Configuration;

class DefaultResolver extends Resolver
{
    public static function instance(): DefaultResolver
    {
        static $instance;

        return $instance ??= new self();
    }

    /**
     * @inheritDoc
     */
    public function retrieveValue(string $variableName, ?string $default = ''): ?string
    {
        return $default;
    }

    public function hasVariable(string $variableName): bool
    {
        return true;
    }
}
