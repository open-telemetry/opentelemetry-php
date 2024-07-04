<?php

namespace OpenTelemetry\API\Instrumentation\AutoInstrumentation;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\ContextKeyInterface;

trait EnableTrait
{
    public static function enable(ContextInterface $context): ContextInterface
    {
        return $context->with(self::contextKey(), true);
    }

    public static function disable(ContextInterface $context): ContextInterface
    {
        return $context->with(self::contextKey(), false);
    }

    /**
     * @internal
     */
    public static function contextKey(): ContextKeyInterface
    {
        static $contextKey;
        return $contextKey ??= Context::createKey(self::class);
    }
}