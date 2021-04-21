<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

trait ContextValueTrait
{
    /**
     * @param Context $context
     * @return mixed|null
     * @phan-suppress PhanAbstractStaticMethodCallInTrait
     */
    public static function extract(Context $context)
    {
        try {
            return $context->get(static::getContextKey());
        } catch (ContextValueNotFoundException $e) {
            return null;
        }
    }

    /**
     * @param mixed $value
     * @param Context $context
     * @return Context
     * @phan-suppress PhanAbstractStaticMethodCallInTrait
     */
    public static function insert($value, Context $context): Context
    {
        return $context->set(static::getContextKey(), $value);
    }

    /**
     * @param mixed $value
     * @return Scope
     * @phan-suppress PhanAbstractStaticMethodCallInTrait
     */
    public static function setCurrent($value): Scope
    {
        $context = Context::getCurrent()->set(static::getContextKey(), $value);

        return new Scope(Context::attach($context));
    }

    /**
     * @return mixed|null
     * @phan-suppress PhanAbstractStaticMethodCallInTrait
     */
    public static function getCurrent()
    {
        try {
            return Context::getCurrent()->get(static::getContextKey());
        } catch (ContextValueNotFoundException $e) {
            return null;
        }
    }

    abstract protected static function getContextKey(): ContextKey;
}
