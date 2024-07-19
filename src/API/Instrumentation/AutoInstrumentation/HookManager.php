<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\AutoInstrumentation;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\ContextKeyInterface;

class HookManager
{
    public static function enable(?ContextInterface $context = null): ContextInterface
    {
        $context ??= Context::getCurrent();

        return $context->with(self::contextKey(), true);
    }

    public static function disable(?ContextInterface $context = null): ContextInterface
    {
        $context ??= Context::getCurrent();

        return $context->with(self::contextKey(), false);
    }

    public static function disabled(?ContextInterface $context = null): bool
    {
        $context ??= Context::getCurrent();

        return $context->get(self::contextKey()) === false;
    }

    private static function contextKey(): ContextKeyInterface
    {
        static $contextKey;

        return $contextKey ??= Context::createKey(self::class);
    }
}
