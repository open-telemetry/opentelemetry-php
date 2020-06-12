<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

class Context
{
    /**
     * @var ContextKey|null
     */
    private $key;

    /**
     * @var mixed|null
     */
    private $value;

    /**
     * @var Context|null
     */
    private $parent;

    private static $current_context = null;

    public function __construct(ContextKey $key=null, $value=null, ?Context $parent=null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->parent = $parent;
    }

    /**
     * @param ContextKey $key
     * @param mixed $value
     *
     * @return Context
     */
    public static function setValue(ContextKey $key, $value, ?Context $parent=null): Context
    {
        if (null === $parent) {
            return self::$current_context = new Context($key, $value, Context::getCurrent());
        }

        return new Context($key, $value, $parent);
    }

    public function set(ContextKey $key, $value): Context
    {
        return new Context($key, $value, $this);
    }

    /**
     * @param ContextKey $key
     *
     * @return mixed
     */
    public function get(ContextKey $key)
    {
        if ($this->key === $key) {
            return $this->value;
        }
        if (null === $this->parent) {
            throw new ContextValueNotFoundException();
        }

        return $this->parent->get($key);
    }

    public static function getValue(ContextKey $key, ?Context $ctx=null)
    {
        $ctx = $ctx ?? Context::getCurrent();

        return $ctx->get($key);
    }

    /**
     * @return Context
     */
    public static function getCurrent(): Context
    {
        if (null === self::$current_context) {
            self::$current_context = new Context();
        }

        return self::$current_context;
    }

    /**
     * @return callable
     */
    public static function attach(Context $ctx): callable
    {
        $former_ctx = self::$current_context;
        self::$current_context = $ctx;

        return function () use ($former_ctx) {
            return $former_ctx;
        };
    }

    /**
     */
    public static function detach(callable $token): Context
    {
        return self::$current_context = call_user_func($token);
    }

    /**
     * @param Context $parent
     *
     * @return null
     */
    protected function setParent(Context $parent)
    {
        $this->parent = $parent;
    }
}
