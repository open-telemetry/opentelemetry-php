<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

trait ContextValueTrait
{
    /**
     * @param mixed $value
     * @param Context|null $context
     * @return Scope
     */
    public static function setCurrent($value, ?Context $context = null): Scope
    {
        $context = $context ?? Context::getCurrent()->set(static::getContextKey(), $value);

        return new Scope(Context::attach($context));
    }

    /**
     * @param Context|null $context
     * @return mixed|null
     */
    public static function getCurrent(?Context $context = null)
    {
        try {
            return ($context ?? Context::getCurrent())->get(static::getContextKey());
        } catch (ContextValueNotFoundException $e) {
            return null;
        }
    }

    abstract protected static function getContextKey(): ContextKey;
}
